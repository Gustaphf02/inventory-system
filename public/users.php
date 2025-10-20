<?php
require_once __DIR__ . '/.auth.php';
// Solo Admin
requireRole(['admin']);
/**
 * Sistema de Inventario - Gestión de Usuarios y Permisos
 * Administración completa de usuarios, roles y permisos
 */

// Datos de ejemplo de usuarios
$users = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@inventory.com',
        'first_name' => 'Administrador',
        'last_name' => 'Sistema',
        'role' => 'admin',
        'is_active' => true,
        'last_login' => '2024-01-15 10:30:00',
        'created_at' => '2024-01-01 00:00:00',
        'permissions' => ['all']
    ],
    [
        'id' => 2,
        'username' => 'manager1',
        'email' => 'manager@inventory.com',
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'role' => 'manager',
        'is_active' => true,
        'last_login' => '2024-01-14 15:45:00',
        'created_at' => '2024-01-02 00:00:00',
        'permissions' => ['products', 'categories', 'suppliers', 'reports']
    ],
    [
        'id' => 3,
        'username' => 'employee1',
        'email' => 'employee@inventory.com',
        'first_name' => 'María',
        'last_name' => 'García',
        'role' => 'employee',
        'is_active' => true,
        'last_login' => '2024-01-15 09:15:00',
        'created_at' => '2024-01-03 00:00:00',
        'permissions' => ['products_view', 'stock_update']
    ],
    [
        'id' => 4,
        'username' => 'viewer1',
        'email' => 'viewer@inventory.com',
        'first_name' => 'Carlos',
        'last_name' => 'López',
        'role' => 'viewer',
        'is_active' => false,
        'last_login' => '2024-01-10 14:20:00',
        'created_at' => '2024-01-04 00:00:00',
        'permissions' => ['products_view']
    ]
];

// Roles y permisos del sistema
$roles = [
    'admin' => [
        'name' => 'Administrador',
        'description' => 'Acceso completo al sistema',
        'permissions' => [
            'users_manage', 'products_manage', 'categories_manage', 'suppliers_manage',
            'reports_generate', 'settings_manage', 'backup_manage', 'system_config'
        ]
    ],
    'manager' => [
        'name' => 'Gerente',
        'description' => 'Gestión de inventario y reportes',
        'permissions' => [
            'products_manage', 'categories_manage', 'suppliers_manage', 'reports_generate'
        ]
    ],
    'employee' => [
        'name' => 'Empleado',
        'description' => 'Operaciones básicas de inventario',
        'permissions' => [
            'products_view', 'stock_update', 'movements_view'
        ]
    ],
    'viewer' => [
        'name' => 'Visualizador',
        'description' => 'Solo lectura del sistema',
        'permissions' => [
            'products_view', 'reports_view'
        ]
    ]
];

// Permisos disponibles
$availablePermissions = [
    'users_manage' => 'Gestionar usuarios',
    'products_manage' => 'Gestionar productos',
    'products_view' => 'Ver productos',
    'categories_manage' => 'Gestionar categorías',
    'suppliers_manage' => 'Gestionar proveedores',
    'stock_update' => 'Actualizar stock',
    'movements_view' => 'Ver movimientos',
    'reports_generate' => 'Generar reportes',
    'reports_view' => 'Ver reportes',
    'settings_manage' => 'Gestionar configuración',
    'backup_manage' => 'Gestionar respaldos',
    'system_config' => 'Configuración del sistema'
];

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_user':
            $newUser = [
                'id' => count($users) + 1,
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'role' => $_POST['role'],
                'is_active' => isset($_POST['is_active']),
                'last_login' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'permissions' => $roles[$_POST['role']]['permissions']
            ];
            $users[] = $newUser;
            $message = "Usuario creado exitosamente";
            break;
            
        case 'update_user':
            $userId = $_POST['user_id'];
            foreach ($users as &$user) {
                if ($user['id'] == $userId) {
                    $user['first_name'] = $_POST['first_name'];
                    $user['last_name'] = $_POST['last_name'];
                    $user['email'] = $_POST['email'];
                    $user['role'] = $_POST['role'];
                    $user['is_active'] = isset($_POST['is_active']);
                    $user['permissions'] = $roles[$_POST['role']]['permissions'];
                    break;
                }
            }
            $message = "Usuario actualizado exitosamente";
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'];
            $users = array_filter($users, function($user) use ($userId) {
                return $user['id'] != $userId;
            });
            $message = "Usuario eliminado exitosamente";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            transition: transform 0.2s ease;
        }
        .user-card:hover {
            transform: translateY(-2px);
        }
        .role-badge {
            font-size: 0.8rem;
        }
        .permission-item {
            font-size: 0.9rem;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            border-radius: 4px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar bg-white shadow-sm" style="min-height: 100vh;">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-primary">
                            <i class="fas fa-users me-2"></i>
                            Usuarios
                        </h4>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#users-list">
                                <i class="fas fa-list me-2"></i>
                                Lista de Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#create-user">
                                <i class="fas fa-user-plus me-2"></i>
                                Crear Usuario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#roles-permissions">
                                <i class="fas fa-shield-alt me-2"></i>
                                Roles y Permisos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#audit-log">
                                <i class="fas fa-history me-2"></i>
                                Registro de Auditoría
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="demo.php" class="btn btn-outline-primary btn-sm mb-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver al Sistema
                        </a>
                        <br>
                        <a href="config.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-cog me-1"></i>
                            Configuración
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Usuarios y Permisos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="exportUsers()">
                                <i class="fas fa-download me-1"></i>
                                Exportar
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="fas fa-user-plus me-1"></i>
                                Nuevo Usuario
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Lista de Usuarios -->
                    <div class="tab-pane fade show active" id="users-list">
                        <div class="row">
                            <?php foreach ($users as $user): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card user-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                </h5>
                                                <p class="text-muted mb-0">@<?= htmlspecialchars($user['username']) ?></p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?> role-badge">
                                                    <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?= htmlspecialchars($user['email']) ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="badge bg-primary role-badge">
                                                <?= htmlspecialchars($roles[$user['role']]['name']) ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Último acceso: <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= $user['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="viewPermissions(<?= $user['id'] ?>)">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <?php if ($user['id'] != 1): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Crear Usuario -->
                    <div class="tab-pane fade" id="create-user">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Crear Nuevo Usuario
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_user">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre de Usuario</label>
                                                <input type="text" class="form-control" name="username" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="first_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Apellido</label>
                                                <input type="text" class="form-control" name="last_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Rol</label>
                                                <select class="form-select" name="role" required>
                                                    <?php foreach ($roles as $roleKey => $role): ?>
                                                    <option value="<?= $roleKey ?>"><?= htmlspecialchars($role['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Estado</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" checked>
                                                    <label class="form-check-label">Usuario Activo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Crear Usuario
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Roles y Permisos -->
                    <div class="tab-pane fade" id="roles-permissions">
                        <div class="row">
                            <?php foreach ($roles as $roleKey => $role): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <?= htmlspecialchars($role['name']) ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3"><?= htmlspecialchars($role['description']) ?></p>
                                        
                                        <h6>Permisos:</h6>
                                        <div class="permissions-list">
                                            <?php foreach ($role['permissions'] as $permission): ?>
                                            <span class="badge bg-secondary permission-item">
                                                <?= htmlspecialchars($availablePermissions[$permission] ?? $permission) ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editRole('<?= $roleKey ?>')">
                                                <i class="fas fa-edit me-1"></i>
                                                Editar Rol
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Registro de Auditoría -->
                    <div class="tab-pane fade" id="audit-log">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Registro de Auditoría
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha/Hora</th>
                                                <th>Usuario</th>
                                                <th>Acción</th>
                                                <th>Detalles</th>
                                                <th>IP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>2024-01-15 10:30:00</td>
                                                <td>admin</td>
                                                <td>Login</td>
                                                <td>Inicio de sesión exitoso</td>
                                                <td>192.168.1.100</td>
                                            </tr>
                                            <tr>
                                                <td>2024-01-15 10:25:00</td>
                                                <td>manager1</td>
                                                <td>Product Update</td>
                                                <td>Actualizó stock del producto RES-1K-1/4W</td>
                                                <td>192.168.1.101</td>
                                            </tr>
                                            <tr>
                                                <td>2024-01-15 09:15:00</td>
                                                <td>employee1</td>
                                                <td>Stock Movement</td>
                                                <td>Movimiento de entrada: +100 unidades</td>
                                                <td>192.168.1.102</td>
                                            </tr>
                                            <tr>
                                                <td>2024-01-14 15:45:00</td>
                                                <td>manager1</td>
                                                <td>Report Generated</td>
                                                <td>Generó reporte de inventario mensual</td>
                                                <td>192.168.1.101</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="role" id="edit_role" required>
                                <?php foreach ($roles as $roleKey => $role): ?>
                                <option value="<?= $roleKey ?>"><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                <label class="form-check-label">Usuario Activo</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="editUserForm" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(userId) {
            // Aquí se cargarían los datos del usuario desde el servidor
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_first_name').value = 'Nombre';
            document.getElementById('edit_last_name').value = 'Apellido';
            document.getElementById('edit_email').value = 'email@example.com';
            document.getElementById('edit_role').value = 'employee';
            document.getElementById('edit_is_active').checked = true;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function deleteUser(userId) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewPermissions(userId) {
            alert('Ver permisos del usuario ' + userId);
        }

        function editRole(roleKey) {
            alert('Editar rol: ' + roleKey);
        }

        function exportUsers() {
            alert('Exportando lista de usuarios...');
        }
    </script>
</body>
</html>
