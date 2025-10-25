<?php
// Archivo de prueba para verificar extensiones PHP en Render
header('Content-Type: application/json');

$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    'pgsql' => extension_loaded('pgsql'),
    'json' => extension_loaded('json'),
    'mbstring' => extension_loaded('mbstring'),
    'openssl' => extension_loaded('openssl'),
    'curl' => extension_loaded('curl'),
    'xml' => extension_loaded('xml')
];

$allExtensions = get_loaded_extensions();
$postgresqlExtensions = array_filter($allExtensions, function($ext) {
    return strpos($ext, 'pgsql') !== false || strpos($ext, 'pdo') !== false;
});

echo json_encode([
    'php_version' => phpversion(),
    'extensions_loaded' => $extensions,
    'postgresql_extensions' => array_values($postgresqlExtensions),
    'all_extensions' => $allExtensions,
    'database_url_configured' => !empty($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL')),
    'database_url_preview' => $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ? substr($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL'), 0, 30) . '...' : 'Not configured'
], JSON_PRETTY_PRINT);
