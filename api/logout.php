<?php
// Wrapper para logout.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/logout.php';
$_SERVER['PHP_SELF'] = '/logout.php';

$publicLogoutPath = $publicDir . '/logout.php';

if (file_exists($publicLogoutPath)) {
    require_once $publicLogoutPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: logout.php no encontrado</h1></body></html>';
}

