<?php
session_start();

// Si ya está logueado, redirigir al sistema
if (isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'employee';
    
    if (empty($email) || empty($password) || empty($name)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        // En un sistema real, aquí se validaría y guardaría en la base de datos
        // Por ahora, solo mostramos un mensaje de éxito
        $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-warehouse fa-3x text-primary mb-3"></i>
                            <h3 class="fw-bold">Registro</h3>
                            <p class="text-muted">Crea tu cuenta en el sistema</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-success">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        Ir al Login
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nombre Completo
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Contraseña
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag me-1"></i>
                                    Rol
                                </label>
                                <select class="form-select" id="role" name="role">
                                    <option value="employee" <?= ($_POST['role'] ?? '') === 'employee' ? 'selected' : '' ?>>
                                        Empleado
                                    </option>
                                    <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>>
                                        Gerente
                                    </option>
                                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                        Administrador
                                    </option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        El rol determina qué funciones puedes acceder en el sistema
                                    </small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Registrarse
                                </button>
                            </div>
                        </form>

                        <?php endif; ?>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">¿Ya tienes cuenta?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Iniciar Sesión
                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
