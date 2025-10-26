<?php
// Configurar cookies de sesión ANTES de iniciar la sesión
ini_set('session.cookie_lifetime', 86400); // 24 horas
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 86400); // 24 horas

session_start();

// Versión simplificada sin SystemLogger para evitar errores 503
// TODO: Restaurar SystemLogger cuando se resuelvan los problemas de permisos

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
        $_SESSION['user'] = [
            'email' => $email,
            'name' => $users[$email][1],
            'role' => $users[$email][2],
            'username' => $users[$email][3]
        ];
        
        // Log simple del login exitoso (sin SystemLogger)
        error_log("LOGIN_SUCCESS: " . $users[$email][3] . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        // Verificar que la sesión se guardó
        if (isset($_SESSION['user'])) {
            error_log("LOGIN_SUCCESS - Session saved: " . json_encode($_SESSION['user']));
        } else {
            error_log("LOGIN_SUCCESS - Session NOT saved!");
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
                            <a href="demo.php" class="text-decoration-none">
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


