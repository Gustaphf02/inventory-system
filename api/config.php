<?php
// Wrapper para config.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/config.php';
$_SERVER['PHP_SELF'] = '/config.php';

header('Content-Type: text/html; charset=utf-8');

$publicConfigPath = $publicDir . '/config.php';

if (file_exists($publicConfigPath)) {
    require_once $publicConfigPath;
} else {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: config.php no encontrado</h1></body></html>';
}

