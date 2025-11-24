<?php
// Wrapper para logout.php en Vercel Serverless Functions
$publicLogoutPath = __DIR__ . '/../public/logout.php';

if (file_exists($publicLogoutPath)) {
    // Cambiar al directorio public para que las rutas relativas funcionen
    chdir(__DIR__ . '/../public');
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
    $_SERVER['SCRIPT_NAME'] = '/logout.php';
    
    require_once $publicLogoutPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: logout.php no encontrado</h1></body></html>';
}

