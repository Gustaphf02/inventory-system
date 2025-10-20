<?php

namespace Inventory\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorizedResponse();
        }
        
        $token = $matches[1];
        
        try {
            $jwtSecret = $_ENV['JWT_SECRET'] ?? 'default-secret';
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            
            // Agregar información del usuario al request
            $request = $request->withAttribute('user_id', $decoded->user_id);
            $request = $request->withAttribute('user_role', $decoded->role);
            $request = $request->withAttribute('user_email', $decoded->email);
            
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            return $this->unauthorizedResponse();
        }
    }
    
    private function unauthorizedResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Token de autenticación inválido o faltante'
        ]));
        
        return $response->withStatus(401)
                       ->withHeader('Content-Type', 'application/json');
    }
}
