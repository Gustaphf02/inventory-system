<?php
// Login.php para Vercel Serverless Functions
// Copiado desde public/login.php con ajustes para Vercel

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
            
            error_log("LOGIN: Session saved successfully");
            
            // Usar header de redirección en lugar de JavaScript
            header('Location: /');
            exit;
        } catch (Exception $e) {
            error_log("LOGIN ERROR: " . $e->getMessage());
            $error = 'Error al iniciar sesión: ' . $e->getMessage();
        }
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-boxes"></i>
            <h2>Sistema de Inventario</h2>
            <p class="text-muted">Inicia sesión para continuar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login.php">
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>Email
                </label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Contraseña
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <small class="text-muted">
                <strong>Usuarios de prueba:</strong><br>
                admin@inventory.com / admin123<br>
                manager@inventory.com / manager123
            </small>
        </div>
    </div>
</body>
</html>
