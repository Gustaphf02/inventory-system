<?php
// Wrapper para login.php en Vercel Serverless Functions
$publicLoginPath = __DIR__ . '/../public/login.php';

if (file_exists($publicLoginPath)) {
    // Cambiar al directorio public para que las rutas relativas funcionen
    chdir(__DIR__ . '/../public');
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
    $_SERVER['SCRIPT_NAME'] = '/login.php';
    
    require_once $publicLoginPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: login.php no encontrado</h1></body></html>';
}

