<?php
// Punto de entrada para login.php en Vercel Serverless Functions
// Este archivo ajusta el entorno para que public/login.php funcione correctamente

// Cambiar el directorio de trabajo al directorio public
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

// Ajustar $_SERVER para que las rutas funcionen
$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/login.php';

// Incluir el archivo de login
$loginPath = $publicDir . '/login.php';

if (file_exists($loginPath)) {
    require_once $loginPath;
} else {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error: Archivo login.php no encontrado</h1>';
    echo '<p>Ruta buscada: ' . htmlspecialchars($loginPath) . '</p>';
    echo '</body></html>';
}

