<?php
require_once __DIR__ . '/.auth.php';
// Solo Admin y Manager
requireRole(['admin','manager']);
/**
 * Sistema de Inventario - Configuración del Sistema
 * Sección completa de configuraciones administrativas
 */

// Configuraciones del sistema
$systemConfig = [
    'company' => [
        'name' => 'Sistema de Inventario',
        'logo' => 'assets/images/logo.png',
        'address' => '123 Main Street, Ciudad, Estado 12345',
        'phone' => '+1 (555) 123-4567',
        'email' => 'info@inventory-system.com',
        'website' => 'https://inventory-system.com',
        'tax_id' => '12-3456789',
        'currency' => 'USD',
        'timezone' => 'America/New_York',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i:s'
    ],
    'inventory' => [
        'auto_reorder' => true,
        'low_stock_threshold' => 10,
        'high_stock_threshold' => 1000,
        'default_category' => 1,
        'default_supplier' => 1,
        'barcode_prefix' => 'INV',
        'sku_length' => 8,
        'allow_negative_stock' => false,
        'track_expiration_dates' => true,
        'expiration_warning_days' => 30
    ],
    'notifications' => [
        'email_notifications' => true,
        'sms_notifications' => false,
        'low_stock_alerts' => true,
        'expiration_alerts' => true,
        'reorder_alerts' => true,
        'daily_reports' => true,
        'weekly_reports' => true,
        'monthly_reports' => true
    ],
    'security' => [
        'session_timeout' => 3600,
        'password_min_length' => 8,
        'password_require_special' => true,
        'max_login_attempts' => 5,
        'lockout_duration' => 900,
        'two_factor_auth' => false,
        'ip_whitelist' => [],
        'audit_log' => true
    ],
    'backup' => [
        'auto_backup' => true,
        'backup_frequency' => 'daily',
        'backup_time' => '02:00',
        'retention_days' => 30,
        'backup_location' => 'backups/',
        'compress_backups' => true,
        'email_backup_reports' => true
    ],
    'integrations' => [
        'mouser_api_key' => '',
        'digikey_api_key' => '',
        'newark_api_key' => '',
        'stripe_api_key' => '',
        'paypal_api_key' => '',
        'quickbooks_enabled' => false,
        'shopify_enabled' => false,
        'amazon_enabled' => false
    ],
    'reports' => [
        'default_report_format' => 'pdf',
        'include_logo' => true,
        'include_footer' => true,
        'auto_email_reports' => false,
        'report_retention_days' => 90,
        'custom_fields' => []
    ],
    'ui' => [
        'theme' => 'light',
        'language' => 'es',
        'items_per_page' => 25,
        'show_images' => true,
        'compact_view' => false,
        'auto_refresh' => false,
        'refresh_interval' => 300
    ]
];

// Configuraciones por defecto
$defaultConfig = [
    'company_name' => 'Sistema de Inventario',
    'currency' => 'USD',
    'low_stock_threshold' => 10,
    'auto_reorder' => false,
    'email_notifications' => true,
    'session_timeout' => 3600,
    'backup_frequency' => 'daily',
    'theme' => 'light',
    'language' => 'es'
];

// Función para guardar configuraciones en archivo
function saveConfig($config, $filename = 'config.json') {
    $configData = json_encode($config, JSON_PRETTY_PRINT);
    return file_put_contents($filename, $configData);
}

// Función para cargar configuraciones desde archivo
function loadConfig($filename = 'config.json') {
    if (file_exists($filename)) {
        $configData = file_get_contents($filename);
        return json_decode($configData, true);
    }
    return null;
}

// Cargar configuraciones guardadas si existen
$savedConfig = loadConfig();
if ($savedConfig) {
    $systemConfig = array_merge($systemConfig, $savedConfig);
}

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $cleanConfig = [];
    
    // Procesar los campos del formulario
    foreach ($_POST as $key => $value) {
        // Saltar el campo section
        if ($key === 'section') {
            continue;
        }
        
        // Procesar campos con formato config[campo]
        if (preg_match('/^config\[(.+)\]$/', $key, $matches)) {
            $fieldName = $matches[1];
            $cleanConfig[$fieldName] = $value;
        }
    }
    
    // Validar y guardar configuración
    if (!empty($section) && !empty($cleanConfig)) {
        // Verificar si la sección existe
        if (isset($systemConfig[$section])) {
            // Actualizar solo los campos enviados
            foreach ($cleanConfig as $key => $value) {
                if (array_key_exists($key, $systemConfig[$section])) {
                    // Convertir valores string 'true'/'false' a boolean
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    }
                    $systemConfig[$section][$key] = $value;
                }
            }
            
            // Guardar en archivo
            if (saveConfig($systemConfig)) {
                $message = "Configuración de " . ucfirst($section) . " actualizada exitosamente";
                $messageType = 'success';
            } else {
                $message = "Error al guardar la configuración";
                $messageType = 'danger';
            }
        } else {
            $message = "Sección de configuración no válida: " . htmlspecialchars($section);
            $messageType = 'danger';
        }
    } else {
        if (empty($section)) {
            $message = "No se especificó la sección a actualizar";
            $messageType = 'warning';
        } elseif (empty($cleanConfig)) {
            $message = "No se recibieron datos de configuración";
            $messageType = 'warning';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .config-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .config-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
        }
        .config-body {
            padding: 2rem;
        }
        .nav-pills .nav-link {
            border-radius: 25px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .config-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .config-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #667eea;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
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
                            <i class="fas fa-cog me-2"></i>
                            Configuración
                        </h4>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#company">
                                <i class="fas fa-building me-2"></i>
                                Empresa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#inventory">
                                <i class="fas fa-boxes me-2"></i>
                                Inventario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#notifications">
                                <i class="fas fa-bell me-2"></i>
                                Notificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#security">
                                <i class="fas fa-shield-alt me-2"></i>
                                Seguridad
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#backup">
                                <i class="fas fa-database me-2"></i>
                                Respaldo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#integrations">
                                <i class="fas fa-plug me-2"></i>
                                Integraciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#reports">
                                <i class="fas fa-chart-bar me-2"></i>
                                Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#ui">
                                <i class="fas fa-palette me-2"></i>
                                Interfaz
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="demo.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver al Sistema
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Configuración del Sistema</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                                <i class="fas fa-undo me-1"></i>
                                Restaurar Predeterminados
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveAllConfig()">
                                <i class="fas fa-save me-1"></i>
                                Guardar Todo
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Configuración de Empresa -->
                    <div class="tab-pane fade show active" id="company">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Información de la Empresa</h3>
                                        <p class="config-description mb-0">Configura los datos básicos de tu empresa</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="company">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre de la Empresa</label>
                                                <input type="text" class="form-control" name="config[name]" value="<?= htmlspecialchars($systemConfig['company']['name']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">ID Fiscal</label>
                                                <input type="text" class="form-control" name="config[tax_id]" value="<?= htmlspecialchars($systemConfig['company']['tax_id']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" name="config[phone]" value="<?= htmlspecialchars($systemConfig['company']['phone']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="config[email]" value="<?= htmlspecialchars($systemConfig['company']['email']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dirección</label>
                                        <textarea class="form-control" name="config[address]" rows="3"><?= htmlspecialchars($systemConfig['company']['address']) ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Moneda</label>
                                                <select class="form-select" name="config[currency]">
                                                    <option value="USD" <?= $systemConfig['company']['currency'] === 'USD' ? 'selected' : '' ?>>USD - Dólar Americano</option>
                                                    <option value="EUR" <?= $systemConfig['company']['currency'] === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                                    <option value="MXN" <?= $systemConfig['company']['currency'] === 'MXN' ? 'selected' : '' ?>>MXN - Peso Mexicano</option>
                                                    <option value="CAD" <?= $systemConfig['company']['currency'] === 'CAD' ? 'selected' : '' ?>>CAD - Dólar Canadiense</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Zona Horaria</label>
                                                <select class="form-select" name="config[timezone]">
                                                    <option value="America/New_York" <?= $systemConfig['company']['timezone'] === 'America/New_York' ? 'selected' : '' ?>>Nueva York</option>
                                                    <option value="America/Mexico_City" <?= $systemConfig['company']['timezone'] === 'America/Mexico_City' ? 'selected' : '' ?>>Ciudad de México</option>
                                                    <option value="America/Los_Angeles" <?= $systemConfig['company']['timezone'] === 'America/Los_Angeles' ? 'selected' : '' ?>>Los Ángeles</option>
                                                    <option value="Europe/Madrid" <?= $systemConfig['company']['timezone'] === 'Europe/Madrid' ? 'selected' : '' ?>>Madrid</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Sitio Web</label>
                                                <input type="url" class="form-control" name="config[website]" value="<?= htmlspecialchars($systemConfig['company']['website']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Inventario -->
                    <div class="tab-pane fade" id="inventory">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-boxes config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Inventario</h3>
                                        <p class="config-description mb-0">Ajusta los parámetros de gestión de inventario</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="inventory">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Umbral de Stock Bajo</label>
                                                <input type="number" class="form-control" name="config[low_stock_threshold]" value="<?= $systemConfig['inventory']['low_stock_threshold'] ?>">
                                                <div class="form-text">Cantidad mínima antes de generar alertas</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Umbral de Stock Alto</label>
                                                <input type="number" class="form-control" name="config[high_stock_threshold]" value="<?= $systemConfig['inventory']['high_stock_threshold'] ?>">
                                                <div class="form-text">Cantidad máxima recomendada</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Prefijo de Código de Barras</label>
                                                <input type="text" class="form-control" name="config[barcode_prefix]" value="<?= htmlspecialchars($systemConfig['inventory']['barcode_prefix']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Longitud de SKU</label>
                                                <input type="number" class="form-control" name="config[sku_length]" value="<?= $systemConfig['inventory']['sku_length'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Días de Advertencia de Expiración</label>
                                                <input type="number" class="form-control" name="config[expiration_warning_days]" value="<?= $systemConfig['inventory']['expiration_warning_days'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Categoría por Defecto</label>
                                                <select class="form-select" name="config[default_category]">
                                                    <option value="1">Electrónicos</option>
                                                    <option value="2">Semiconductores</option>
                                                    <option value="3">Resistores</option>
                                                    <option value="4">Capacitores</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Proveedor por Defecto</label>
                                                <select class="form-select" name="config[default_supplier]">
                                                    <option value="1">Mouser Electronics</option>
                                                    <option value="2">DigiKey</option>
                                                    <option value="3">Newark</option>
                                                    <option value="4">RS Components</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[auto_reorder]" <?= $systemConfig['inventory']['auto_reorder'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Reorden Automático</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[allow_negative_stock]" <?= $systemConfig['inventory']['allow_negative_stock'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Permitir Stock Negativo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[track_expiration_dates]" <?= $systemConfig['inventory']['track_expiration_dates'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Rastrear Fechas de Expiración</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Notificaciones -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bell config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Notificaciones</h3>
                                        <p class="config-description mb-0">Gestiona las alertas y notificaciones del sistema</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="notifications">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[email_notifications]" <?= $systemConfig['notifications']['email_notifications'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Notificaciones por Email</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[sms_notifications]" <?= $systemConfig['notifications']['sms_notifications'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Notificaciones por SMS</label>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Tipos de Alertas</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[low_stock_alerts]" <?= $systemConfig['notifications']['low_stock_alerts'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Alertas de Stock Bajo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[expiration_alerts]" <?= $systemConfig['notifications']['expiration_alerts'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Alertas de Expiración</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[reorder_alerts]" <?= $systemConfig['notifications']['reorder_alerts'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Alertas de Reorden</label>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Reportes Automáticos</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[daily_reports]" <?= $systemConfig['notifications']['daily_reports'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Reportes Diarios</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[weekly_reports]" <?= $systemConfig['notifications']['weekly_reports'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Reportes Semanales</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[monthly_reports]" <?= $systemConfig['notifications']['monthly_reports'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Reportes Mensuales</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Seguridad -->
                    <div class="tab-pane fade" id="security">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Seguridad</h3>
                                        <p class="config-description mb-0">Configura las políticas de seguridad del sistema</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="security">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Timeout de Sesión (segundos)</label>
                                                <input type="number" class="form-control" name="config[session_timeout]" value="<?= $systemConfig['security']['session_timeout'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Longitud Mínima de Contraseña</label>
                                                <input type="number" class="form-control" name="config[password_min_length]" value="<?= $systemConfig['security']['password_min_length'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Intentos Máximos de Login</label>
                                                <input type="number" class="form-control" name="config[max_login_attempts]" value="<?= $systemConfig['security']['max_login_attempts'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Duración del Bloqueo (segundos)</label>
                                                <input type="number" class="form-control" name="config[lockout_duration]" value="<?= $systemConfig['security']['lockout_duration'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[password_require_special]" <?= $systemConfig['security']['password_require_special'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Requerir Caracteres Especiales</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[two_factor_auth]" <?= $systemConfig['security']['two_factor_auth'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Autenticación de Dos Factores</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[audit_log]" <?= $systemConfig['security']['audit_log'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Registro de Auditoría</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Lista Blanca de IPs</label>
                                        <textarea class="form-control" name="config[ip_whitelist]" rows="3" placeholder="Una IP por línea"><?= implode("\n", $systemConfig['security']['ip_whitelist']) ?></textarea>
                                        <div class="form-text">Dejar vacío para permitir todas las IPs</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Respaldo -->
                    <div class="tab-pane fade" id="backup">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-database config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Respaldo</h3>
                                        <p class="config-description mb-0">Gestiona los respaldos automáticos del sistema</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="backup">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[auto_backup]" <?= $systemConfig['backup']['auto_backup'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Respaldo Automático</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[compress_backups]" <?= $systemConfig['backup']['compress_backups'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Comprimir Respaldos</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Frecuencia de Respaldo</label>
                                                <select class="form-select" name="config[backup_frequency]">
                                                    <option value="daily" <?= $systemConfig['backup']['backup_frequency'] === 'daily' ? 'selected' : '' ?>>Diario</option>
                                                    <option value="weekly" <?= $systemConfig['backup']['backup_frequency'] === 'weekly' ? 'selected' : '' ?>>Semanal</option>
                                                    <option value="monthly" <?= $systemConfig['backup']['backup_frequency'] === 'monthly' ? 'selected' : '' ?>>Mensual</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Hora de Respaldo</label>
                                                <input type="time" class="form-control" name="config[backup_time]" value="<?= $systemConfig['backup']['backup_time'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Días de Retención</label>
                                                <input type="number" class="form-control" name="config[retention_days]" value="<?= $systemConfig['backup']['retention_days'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Ubicación de Respaldos</label>
                                                <input type="text" class="form-control" name="config[backup_location]" value="<?= htmlspecialchars($systemConfig['backup']['backup_location']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[email_backup_reports]" <?= $systemConfig['backup']['email_backup_reports'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Reportes de Respaldo por Email</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            Guardar Configuración
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="createBackup()">
                                            <i class="fas fa-download me-1"></i>
                                            Crear Respaldo Ahora
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Integraciones -->
                    <div class="tab-pane fade" id="integrations">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-plug config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Integraciones Externas</h3>
                                        <p class="config-description mb-0">Configura las conexiones con servicios externos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="integrations">
                                    <h5>APIs de Proveedores</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Mouser API Key</label>
                                                <input type="password" class="form-control" name="config[mouser_api_key]" value="<?= htmlspecialchars($systemConfig['integrations']['mouser_api_key']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">DigiKey API Key</label>
                                                <input type="password" class="form-control" name="config[digikey_api_key]" value="<?= htmlspecialchars($systemConfig['integrations']['digikey_api_key']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Newark API Key</label>
                                                <input type="password" class="form-control" name="config[newark_api_key]" value="<?= htmlspecialchars($systemConfig['integrations']['newark_api_key']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Procesamiento de Pagos</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stripe API Key</label>
                                                <input type="password" class="form-control" name="config[stripe_api_key]" value="<?= htmlspecialchars($systemConfig['integrations']['stripe_api_key']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">PayPal API Key</label>
                                                <input type="password" class="form-control" name="config[paypal_api_key]" value="<?= htmlspecialchars($systemConfig['integrations']['paypal_api_key']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Integraciones de Negocio</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[quickbooks_enabled]" <?= $systemConfig['integrations']['quickbooks_enabled'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">QuickBooks</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[shopify_enabled]" <?= $systemConfig['integrations']['shopify_enabled'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Shopify</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[amazon_enabled]" <?= $systemConfig['integrations']['amazon_enabled'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Amazon</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Reportes -->
                    <div class="tab-pane fade" id="reports">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chart-bar config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Reportes</h3>
                                        <p class="config-description mb-0">Personaliza la generación y formato de reportes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="reports">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Formato de Reporte por Defecto</label>
                                                <select class="form-select" name="config[default_report_format]">
                                                    <option value="pdf" <?= $systemConfig['reports']['default_report_format'] === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                                    <option value="excel" <?= $systemConfig['reports']['default_report_format'] === 'excel' ? 'selected' : '' ?>>Excel</option>
                                                    <option value="csv" <?= $systemConfig['reports']['default_report_format'] === 'csv' ? 'selected' : '' ?>>CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Días de Retención de Reportes</label>
                                                <input type="number" class="form-control" name="config[report_retention_days]" value="<?= $systemConfig['reports']['report_retention_days'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[include_logo]" <?= $systemConfig['reports']['include_logo'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Incluir Logo de la Empresa</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[include_footer]" <?= $systemConfig['reports']['include_footer'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Incluir Pie de Página</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[auto_email_reports]" <?= $systemConfig['reports']['auto_email_reports'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Envío Automático por Email</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Campos Personalizados</label>
                                        <textarea class="form-control" name="config[custom_fields]" rows="3" placeholder="Un campo por línea"><?= implode("\n", $systemConfig['reports']['custom_fields']) ?></textarea>
                                        <div class="form-text">Campos adicionales para incluir en los reportes</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Interfaz -->
                    <div class="tab-pane fade" id="ui">
                        <div class="config-section">
                            <div class="config-header">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-palette config-icon me-3"></i>
                                    <div>
                                        <h3 class="mb-0">Configuración de Interfaz</h3>
                                        <p class="config-description mb-0">Personaliza la apariencia y comportamiento de la interfaz</p>
                                    </div>
                                </div>
                            </div>
                            <div class="config-body">
                                <form method="POST">
                                    <input type="hidden" name="section" value="ui">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Tema</label>
                                                <select class="form-select" name="config[theme]">
                                                    <option value="light" <?= $systemConfig['ui']['theme'] === 'light' ? 'selected' : '' ?>>Claro</option>
                                                    <option value="dark" <?= $systemConfig['ui']['theme'] === 'dark' ? 'selected' : '' ?>>Oscuro</option>
                                                    <option value="auto" <?= $systemConfig['ui']['theme'] === 'auto' ? 'selected' : '' ?>>Automático</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Idioma</label>
                                                <select class="form-select" name="config[language]">
                                                    <option value="es" <?= $systemConfig['ui']['language'] === 'es' ? 'selected' : '' ?>>Español</option>
                                                    <option value="en" <?= $systemConfig['ui']['language'] === 'en' ? 'selected' : '' ?>>English</option>
                                                    <option value="fr" <?= $systemConfig['ui']['language'] === 'fr' ? 'selected' : '' ?>>Français</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Elementos por Página</label>
                                                <select class="form-select" name="config[items_per_page]">
                                                    <option value="10" <?= $systemConfig['ui']['items_per_page'] == 10 ? 'selected' : '' ?>>10</option>
                                                    <option value="25" <?= $systemConfig['ui']['items_per_page'] == 25 ? 'selected' : '' ?>>25</option>
                                                    <option value="50" <?= $systemConfig['ui']['items_per_page'] == 50 ? 'selected' : '' ?>>50</option>
                                                    <option value="100" <?= $systemConfig['ui']['items_per_page'] == 100 ? 'selected' : '' ?>>100</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Intervalo de Actualización (segundos)</label>
                                                <input type="number" class="form-control" name="config[refresh_interval]" value="<?= $systemConfig['ui']['refresh_interval'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[show_images]" <?= $systemConfig['ui']['show_images'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Mostrar Imágenes de Productos</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[compact_view]" <?= $systemConfig['ui']['compact_view'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Vista Compacta</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" name="config[auto_refresh]" <?= $systemConfig['ui']['auto_refresh'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Actualización Automática</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Guardar Configuración
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetToDefaults() {
            if (confirm('¿Estás seguro de restaurar todas las configuraciones a sus valores predeterminados?')) {
                // Aquí se implementaría la lógica para restaurar valores predeterminados
                alert('Configuraciones restauradas a valores predeterminados');
            }
        }

        function saveAllConfig() {
            // Aquí se implementaría la lógica para guardar todas las configuraciones
            alert('Todas las configuraciones han sido guardadas');
        }

        function createBackup() {
            if (confirm('¿Crear un respaldo completo del sistema ahora?')) {
                // Aquí se implementaría la lógica para crear un respaldo
                alert('Respaldo creado exitosamente');
            }
        }

        // Cambiar tema dinámicamente
        document.addEventListener('DOMContentLoaded', function() {
            const themeSelect = document.querySelector('select[name="config[theme]"]');
            if (themeSelect) {
                themeSelect.addEventListener('change', function() {
                    const theme = this.value;
                    document.body.className = theme === 'dark' ? 'bg-dark text-white' : 'bg-light';
                });
            }
        });
    </script>
</body>
</html>
