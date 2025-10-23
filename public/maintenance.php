<?php
require_once __DIR__ . '/.auth.php';
require_once __DIR__ . '/includes/SystemLogger.php';
// Admin y Manager
requireRole(['admin','manager']);
/**
 * Sistema de Inventario - Respaldo y Mantenimiento
 * Gestión completa de respaldos, logs y mantenimiento del sistema
 */

// Manejar acciones de logs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'export_logs':
            $export = SystemLogger::exportToCSV();
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
            echo $export['content'];
            SystemLogger::logUserActivity('EXPORT_LOGS', "Archivo: " . $export['filename'] . ", Tamaño: " . $export['size'] . " bytes");
            exit;
            
        case 'clear_logs':
            $days = (int)($_POST['days'] ?? 30);
            SystemLogger::clearOldLogs($days);
            SystemLogger::logUserActivity('CLEAR_LOGS', "Eliminados logs de más de $days días");
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=logs_cleared');
            exit;
            
        case 'create_backup':
            $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            SystemLogger::logBackup('MANUAL_BACKUP', $backupName, 0, true);
            SystemLogger::logUserActivity('CREATE_BACKUP', "Respaldo manual creado: $backupName");
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=backup_created');
            exit;
    }
}

// Log del acceso a mantenimiento
SystemLogger::logUserActivity('MAINTENANCE_ACCESS', "Acceso a página de mantenimiento");

// Datos de ejemplo de respaldos
$backups = [
    [
        'id' => 1,
        'filename' => 'backup_2024_01_15_02_00.sql',
        'size' => '15.2 MB',
        'created_at' => '2024-01-15 02:00:00',
        'type' => 'automatic',
        'status' => 'completed',
        'location' => '/backups/automatic/'
    ],
    [
        'id' => 2,
        'filename' => 'backup_2024_01_14_02_00.sql',
        'size' => '14.8 MB',
        'created_at' => '2024-01-14 02:00:00',
        'type' => 'automatic',
        'status' => 'completed',
        'location' => '/backups/automatic/'
    ],
    [
        'id' => 3,
        'filename' => 'backup_manual_2024_01_13.sql',
        'size' => '14.5 MB',
        'created_at' => '2024-01-13 15:30:00',
        'type' => 'manual',
        'status' => 'completed',
        'location' => '/backups/manual/'
    ]
];

// Logs del sistema
$systemLogs = [
    [
        'timestamp' => '2024-01-15 10:30:00',
        'level' => 'INFO',
        'message' => 'Sistema iniciado correctamente',
        'module' => 'System'
    ],
    [
        'timestamp' => '2024-01-15 10:25:00',
        'level' => 'WARNING',
        'message' => 'Stock bajo detectado en producto LED-RED-5MM',
        'module' => 'Inventory'
    ],
    [
        'timestamp' => '2024-01-15 09:15:00',
        'level' => 'ERROR',
        'message' => 'Error al conectar con API de Mouser',
        'module' => 'External API'
    ],
    [
        'timestamp' => '2024-01-15 08:45:00',
        'level' => 'INFO',
        'message' => 'Respaldo automático completado exitosamente',
        'module' => 'Backup'
    ]
];

// Estadísticas del sistema
$systemStats = [
    'database_size' => '45.2 MB',
    'total_users' => 4,
    'total_products' => 8,
    'total_categories' => 8,
    'total_suppliers' => 4,
    'disk_usage' => '2.1 GB',
    'memory_usage' => '128 MB',
    'uptime' => '15 días, 2 horas',
    'last_backup' => '2024-01-15 02:00:00',
    'next_backup' => '2024-01-16 02:00:00'
];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            $message = "Respaldo creado exitosamente";
            break;
        case 'restore_backup':
            $message = "Respaldo restaurado exitosamente";
            break;
        case 'delete_backup':
            $message = "Respaldo eliminado exitosamente";
            break;
        case 'clear_logs':
            $message = "Logs limpiados exitosamente";
            break;
        case 'optimize_database':
            $message = "Base de datos optimizada exitosamente";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo y Mantenimiento - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
            color: #000;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .log-level-info {
            color: #17a2b8;
        }
        .log-level-warning {
            color: #ffc107;
        }
        .log-level-error {
            color: #dc3545;
        }
        .backup-card {
            transition: transform 0.2s ease;
        }
        .backup-card:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
            border: none;
            color: #000;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #B8860B 0%, #8B6914 100%);
            color: #fff;
        }
        .btn-outline-primary {
            color: #FFD700;
            border-color: #FFD700;
        }
        .btn-outline-primary:hover {
            background-color: #FFD700;
            border-color: #FFD700;
            color: #000;
        }
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .text-primary {
            color: #FFD700 !important;
        }
        .progress-ring {
            width: 60px;
            height: 60px;
        }
        .progress-ring circle {
            fill: transparent;
            stroke-width: 4;
        }
        .progress-ring .progress-ring-circle {
            stroke: #667eea;
            stroke-dasharray: 157;
            stroke-dashoffset: 157;
            transition: stroke-dashoffset 0.5s ease;
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
                            <i class="fas fa-tools me-2"></i>
                            Mantenimiento
                        </h4>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#overview">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Resumen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#backups">
                                <i class="fas fa-database me-2"></i>
                                Respaldos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#logs">
                                <i class="fas fa-file-alt me-2"></i>
                                Logs del Sistema
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#maintenance">
                                <i class="fas fa-wrench me-2"></i>
                                Mantenimiento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#monitoring">
                                <i class="fas fa-chart-line me-2"></i>
                                Monitoreo
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">
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
                    <h1 class="h2">Respaldo y Mantenimiento del Sistema</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="refreshStats()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Actualizar
                            </button>
                            <button type="button" class="btn btn-success" onclick="createBackup()">
                                <i class="fas fa-download me-1"></i>
                                Crear Respaldo
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
                    <!-- Resumen del Sistema -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stat-card">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Tamaño de Base de Datos
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $systemStats['database_size'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-database stat-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stat-card">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Uso de Disco
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $systemStats['disk_usage'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hdd stat-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stat-card">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Tiempo de Actividad
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $systemStats['uptime'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock stat-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stat-card">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                Uso de Memoria
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $systemStats['memory_usage'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-memory stat-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-line me-2"></i>
                                            Rendimiento del Sistema
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="performanceChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calendar me-2"></i>
                                            Próximos Mantenimientos
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Respaldo Automático</h6>
                                                    <small class="text-muted"><?= $systemStats['next_backup'] ?></small>
                                                </div>
                                                <span class="badge bg-primary rounded-pill">Automático</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Optimización de BD</h6>
                                                    <small class="text-muted">Cada domingo a las 03:00</small>
                                                </div>
                                                <span class="badge bg-success rounded-pill">Programado</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Limpieza de Logs</h6>
                                                    <small class="text-muted">Cada mes</small>
                                                </div>
                                                <span class="badge bg-warning rounded-pill">Pendiente</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Respaldos -->
                    <div class="tab-pane fade" id="backups">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-database me-2"></i>
                                    Gestión de Respaldos
                                </h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success" onclick="createBackup()">
                                        <i class="fas fa-plus me-1"></i>
                                        Crear Respaldo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="restoreBackup()">
                                        <i class="fas fa-upload me-1"></i>
                                        Restaurar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($backups as $backup): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card backup-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="card-title"><?= htmlspecialchars($backup['filename']) ?></h6>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y H:i', strtotime($backup['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-<?= $backup['type'] === 'automatic' ? 'primary' : 'success' ?>">
                                                        <?= ucfirst($backup['type']) ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-weight me-1"></i>
                                                        Tamaño: <?= $backup['size'] ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-folder me-1"></i>
                                                        <?= htmlspecialchars($backup['location']) ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup(<?= $backup['id'] ?>)">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="restoreBackup(<?= $backup['id'] ?>)">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup(<?= $backup['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs del Sistema -->
                    <div class="tab-pane fade" id="logs">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Logs del Sistema
                                </h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="exportLogs()">
                                        <i class="fas fa-download me-1"></i>
                                        Exportar
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="clearLogs()">
                                        <i class="fas fa-trash me-1"></i>
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="logs-table" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha/Hora</th>
                                                <th>Nivel</th>
                                                <th>Módulo</th>
                                                <th>Usuario</th>
                                                <th>Acción</th>
                                                <th>IP</th>
                                                <th>Detalles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($systemLogs)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No hay logs disponibles
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($systemLogs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small><?= $log['timestamp'] ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $log['level'] === 'INFO' ? 'info' : ($log['level'] === 'WARNING' ? 'warning' : 'danger') ?>">
                                                        <?= $log['level'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($log['module']) ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($log['user']) ?></strong>
                                                </td>
                                                <td>
                                                    <code><?= htmlspecialchars($log['action']) ?></code>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($log['ip']) ?></small>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($log['details']) ?></small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mantenimiento -->
                    <div class="tab-pane fade" id="maintenance">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-database me-2"></i>
                                            Base de Datos
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6>Optimización de Base de Datos</h6>
                                            <p class="text-muted">Mejora el rendimiento de la base de datos</p>
                                            <button class="btn btn-primary" onclick="optimizeDatabase()">
                                                <i class="fas fa-tools me-1"></i>
                                                Optimizar BD
                                            </button>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <h6>Reparación de Tablas</h6>
                                            <p class="text-muted">Repara tablas dañadas</p>
                                            <button class="btn btn-warning" onclick="repairTables()">
                                                <i class="fas fa-wrench me-1"></i>
                                                Reparar Tablas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-broom me-2"></i>
                                            Limpieza del Sistema
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6>Archivos Temporales</h6>
                                            <p class="text-muted">Elimina archivos temporales</p>
                                            <button class="btn btn-info" onclick="cleanTempFiles()">
                                                <i class="fas fa-trash me-1"></i>
                                                Limpiar Temporales
                                            </button>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <h6>Cache del Sistema</h6>
                                            <p class="text-muted">Limpia la cache del sistema</p>
                                            <button class="btn btn-secondary" onclick="clearCache()">
                                                <i class="fas fa-sync me-1"></i>
                                                Limpiar Cache
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monitoreo -->
                    <div class="tab-pane fade" id="monitoring">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-server me-2"></i>
                                            Estado del Servidor
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="progress-ring">
                                                    <svg class="progress-ring">
                                                        <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                                                    </svg>
                                                </div>
                                                <small>CPU: 45%</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="progress-ring">
                                                    <svg class="progress-ring">
                                                        <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                                                    </svg>
                                                </div>
                                                <small>RAM: 68%</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="progress-ring">
                                                    <svg class="progress-ring">
                                                        <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                                                    </svg>
                                                </div>
                                                <small>Disco: 32%</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-network-wired me-2"></i>
                                            Conectividad
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Base de Datos</span>
                                                <span class="badge bg-success rounded-pill">Conectado</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>API Mouser</span>
                                                <span class="badge bg-success rounded-pill">Activo</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>API DigiKey</span>
                                                <span class="badge bg-warning rounded-pill">Limitado</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Servicio de Email</span>
                                                <span class="badge bg-success rounded-pill">Activo</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de rendimiento
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: [{
                    label: 'CPU %',
                    data: [25, 30, 45, 50, 40, 35],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Memoria %',
                    data: [40, 45, 60, 65, 55, 50],
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        function createBackup() {
            if (confirm('¿Crear un respaldo completo del sistema?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="create_backup">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function restoreBackup(backupId) {
            if (confirm('¿Restaurar este respaldo? Esta acción sobrescribirá todos los datos actuales.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="restore_backup">
                    <input type="hidden" name="backup_id" value="${backupId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteBackup(backupId) {
            if (confirm('¿Eliminar este respaldo permanentemente?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_backup">
                    <input type="hidden" name="backup_id" value="${backupId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function downloadBackup(backupId) {
            alert('Descargando respaldo ' + backupId);
        }

        function clearLogs() {
            if (confirm('¿Limpiar todos los logs del sistema?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="clear_logs">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportLogs() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="export_logs">';
            document.body.appendChild(form);
            form.submit();
        }

        function clearLogs() {
            if (confirm('¿Estás seguro de limpiar los logs antiguos? Esto eliminará logs de más de 30 días.')) {
                const days = prompt('¿Cuántos días de logs quieres conservar? (por defecto: 30)', '30');
                if (days !== null) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="clear_logs">
                        <input type="hidden" name="days" value="${days || 30}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        function optimizeDatabase() {
            if (confirm('¿Optimizar la base de datos? Esto puede tomar varios minutos.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="optimize_database">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function repairTables() {
            alert('Reparando tablas de la base de datos...');
        }

        function cleanTempFiles() {
            alert('Limpiando archivos temporales...');
        }

        function clearCache() {
            alert('Limpiando cache del sistema...');
        }

        function refreshStats() {
            location.reload();
        }

        // Actualizar logs en tiempo real
        function refreshLogs() {
            fetch('/api/logs-test.php?action=get_logs&limit=50')
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            updateLogsTable(data.data);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.error('Response was:', text);
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar logs:', error);
                });
        }

        function updateLogsTable(logs) {
            const tbody = document.querySelector('#logs-table tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            
            if (logs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay logs disponibles
                        </td>
                    </tr>
                `;
                return;
            }

            logs.forEach(log => {
                const levelClass = log.level === 'INFO' ? 'info' : 
                                 log.level === 'WARNING' ? 'warning' : 'danger';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><small>${log.timestamp}</small></td>
                    <td><span class="badge bg-${levelClass}">${log.level}</span></td>
                    <td><span class="badge bg-secondary">${log.module}</span></td>
                    <td><strong>${log.user}</strong></td>
                    <td><code>${log.action}</code></td>
                    <td><small>${log.ip}</small></td>
                    <td><small>${log.details}</small></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Actualizar logs cada 30 segundos
        setInterval(refreshLogs, 30000);

        // Actualizar logs al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            refreshLogs();
        });
    </script>
</body>
</html>
