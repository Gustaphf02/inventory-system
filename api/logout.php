<?php
// Punto de entrada para logout.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);
$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/logout.php';

$logoutPath = $publicDir . '/logout.php';
if (file_exists($logoutPath)) {
    require_once $logoutPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error: Archivo logout.php no encontrado</h1>';
    echo '</body></html>';
}

