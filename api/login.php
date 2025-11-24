<?php
// Login.php para Vercel Serverless Functions
// Contenido completo copiado desde public/login.php

// Configurar manejo de errores ANTES de cualquier output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

// Cambiar al directorio public para que las rutas relativas funcionen
$publicDir = __DIR__ . '/../public';
chdir($publicDir);
$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/login.php';
$_SERVER['PHP_SELF'] = '/login.php';

// Configurar headers para HTML ANTES de session_start
header('Content-Type: text/html; charset=utf-8');

session_start();

// CONTROL DE NAVEGACIÓN: Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: /');
    exit;
}

// Demo users (email => [password, name, role, username])
$users = [
    'admin@inventory.com' => ['admin123', 'Administrador Sistema', 'admin', 'admin'],
    'manager@inventory.com' => ['manager123', 'Juan Pérez', 'manager', 'manager1'],
    'employee@inventory.com' => ['employee123', 'María García', 'employee', 'employee1'],
    'viewer@inventory.com' => ['viewer123', 'Carlos López', 'viewer', 'viewer1'],
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (isset($users[$email]) && $users[$email][0] === $password) {
        $userData = [
            'email' => $email,
            'name' => $users[$email][1],
            'role' => $users[$email][2],
            'username' => $users[$email][3]
        ];
        
        // Guardar sesión en base de datos Y en $_SESSION
        try {
            require_once $publicDir . '/DatabaseManager.php';
            $db = DatabaseManager::getInstance();
            
            $sessionId = session_id();
            error_log("LOGIN: Session ID: $sessionId");
            error_log("LOGIN: Saving session to database...");
            
            $db->saveSession($sessionId, $userData);
            $_SESSION['user'] = $userData;
            
            error_log("LOGIN_SUCCESS: User=" . $users[$email][3] . ", Session=$sessionId, IP=" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
        } catch (Exception $e) {
            error_log("LOGIN_ERROR: " . $e->getMessage());
            $_SESSION['user'] = $userData;
        }
        
        // Usar header de redirección en lugar de JavaScript
        header('Location: /');
        exit;
    } else {
        $error = 'Credenciales inválidas';
        
        // Log simple del login fallido (sin SystemLogger)
        error_log("LOGIN_FAILED: " . $email . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-3 text-center"><i class="fas fa-warehouse me-2"></i>Inventario</h3>
                        <p class="text-muted text-center mb-4">Inicie sesión para continuar</p>
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Correo</label>
                                <input type="email" class="form-control" name="email" placeholder="admin@inventory.com" autocomplete="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" placeholder="******" autocomplete="current-password" required>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fas fa-sign-in-alt me-2"></i>Entrar
                            </button>
                        </form>
                        <hr>
                        <div class="small text-muted">
                            <strong>Usuarios de prueba</strong>
                            <ul class="mb-0">
                                <li>admin@inventory.com / admin123</li>
                                <li>manager@inventory.com / manager123</li>
                                <li>employee@inventory.com / employee123</li>
                                <li>viewer@inventory.com / viewer123</li>
                            </ul>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
