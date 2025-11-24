<?php
// Punto de entrada para Vercel Serverless Functions
// Este archivo ajusta el entorno para que public/index.php funcione correctamente

// Cambiar el directorio de trabajo al directorio public
// Esto asegura que las rutas relativas en public/index.php funcionen correctamente
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

// Ajustar $_SERVER['SCRIPT_NAME'] y $_SERVER['DOCUMENT_ROOT'] para que las rutas funcionen
$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/api/index.php';

// Incluir el archivo principal
$publicIndexPath = $publicDir . '/index.php';

if (file_exists($publicIndexPath)) {
    // Incluir el archivo principal
    require_once $publicIndexPath;
} else {
    // Si no existe, devolver error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Archivo principal no encontrado: ' . $publicIndexPath
    ]);
}
