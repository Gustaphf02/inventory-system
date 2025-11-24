<?php
// Wrapper para register.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/register.php';
$_SERVER['PHP_SELF'] = '/register.php';

$publicRegisterPath = $publicDir . '/register.php';

if (file_exists($publicRegisterPath)) {
    require_once $publicRegisterPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: register.php no encontrado</h1></body></html>';
}

