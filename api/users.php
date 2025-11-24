<?php
// Wrapper para users.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/users.php';
$_SERVER['PHP_SELF'] = '/users.php';

header('Content-Type: text/html; charset=utf-8');

$publicUsersPath = $publicDir . '/users.php';

if (file_exists($publicUsersPath)) {
    require_once $publicUsersPath;
} else {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: users.php no encontrado</h1></body></html>';
}

