<?php
// Wrapper para maintenance.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/maintenance.php';
$_SERVER['PHP_SELF'] = '/maintenance.php';

header('Content-Type: text/html; charset=utf-8');

$publicMaintenancePath = $publicDir . '/maintenance.php';

if (file_exists($publicMaintenancePath)) {
    require_once $publicMaintenancePath;
} else {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: maintenance.php no encontrado</h1></body></html>';
}

