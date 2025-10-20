<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Inventory\Config\Database;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                return $response->withStatus(400)->withJson([
                    'success' => false,
                    'message' => 'Email y contraseña son requeridos'
                ]);
            }
            
            $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ]);
            }
            
            // Generar JWT
            $jwtSecret = $_ENV['JWT_SECRET'] ?? 'default-secret';
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + ($_ENV['JWT_EXPIRATION'] ?? 86400)
            ];
            
            $token = JWT::encode($payload, $jwtSecret, 'HS256');
            
            // Actualizar último login
            $updateSql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute(['id' => $user['id']]);
            
            return $response->withJson([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'role' => $user['role']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en el login: ' . $e->getMessage()
            ]);
        }
    }

    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $requiredFields = ['email', 'password', 'first_name', 'last_name'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $response->withStatus(400)->withJson([
                        'success' => false,
                        'message' => "El campo {$field} es requerido"
                    ]);
                }
            }
            
            // Verificar si el email ya existe
            $checkSql = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['email' => $data['email']]);
            
            if ($checkStmt->fetch()) {
                return $response->withStatus(400)->withJson([
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ]);
            }
            
            // Crear usuario
            $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
                    VALUES (:username, :email, :password_hash, :first_name, :last_name, :role)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'username' => $data['email'], // Usar email como username por defecto
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => $data['role'] ?? 'employee'
            ]);
            
            if ($result) {
                return $response->withStatus(201)->withJson([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
                ]);
            } else {
                return $response->withStatus(500)->withJson([
                    'success' => false,
                    'message' => 'Error al registrar usuario'
                ]);
            }
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en el registro: ' . $e->getMessage()
            ]);
        }
    }

    public function refresh(Request $request, Response $response): Response
    {
        try {
            $authHeader = $request->getHeaderLine('Authorization');
            
            if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'message' => 'Token requerido'
                ]);
            }
            
            $token = $matches[1];
            $jwtSecret = $_ENV['JWT_SECRET'] ?? 'default-secret';
            
            try {
                $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
                
                // Generar nuevo token
                $payload = [
                    'user_id' => $decoded->user_id,
                    'email' => $decoded->email,
                    'role' => $decoded->role,
                    'iat' => time(),
                    'exp' => time() + ($_ENV['JWT_EXPIRATION'] ?? 86400)
                ];
                
                $newToken = JWT::encode($payload, $jwtSecret, 'HS256');
                
                return $response->withJson([
                    'success' => true,
                    'data' => ['token' => $newToken]
                ]);
            } catch (\Exception $e) {
                return $response->withStatus(401)->withJson([
                    'success' => false,
                    'message' => 'Token inválido'
                ]);
            }
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al refrescar token: ' . $e->getMessage()
            ]);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        // En una implementación real, podrías invalidar el token en una blacklist
        return $response->withJson([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    public function me(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute('user_id');
            
            $sql = "SELECT id, username, email, first_name, last_name, role, last_login FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener información del usuario: ' . $e->getMessage()
            ]);
        }
    }
}
