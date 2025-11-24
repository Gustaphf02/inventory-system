<?php
// Wrapper para register.php en Vercel Serverless Functions
$publicRegisterPath = __DIR__ . '/../public/register.php';

if (file_exists($publicRegisterPath)) {
    // Cambiar al directorio public para que las rutas relativas funcionen
    chdir(__DIR__ . '/../public');
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
    $_SERVER['SCRIPT_NAME'] = '/register.php';
    
    require_once $publicRegisterPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: register.php no encontrado</h1></body></html>';
}

