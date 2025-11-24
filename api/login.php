<?php
// Wrapper para login.php en Vercel Serverless Functions
// Este archivo ejecuta public/login.php correctamente

// Cambiar al directorio public ANTES de incluir el archivo
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

// Ajustar variables de servidor para que las rutas funcionen
$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/login.php';
$_SERVER['PHP_SELF'] = '/login.php';

// Incluir el archivo principal
$publicLoginPath = $publicDir . '/login.php';

if (file_exists($publicLoginPath)) {
    require_once $publicLoginPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error: login.php no encontrado en: ' . htmlspecialchars($publicLoginPath) . '</h1></body></html>';
}

