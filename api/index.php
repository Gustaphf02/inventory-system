<?php
// Punto de entrada para Vercel Serverless Functions
// Este archivo redirige a public/index.php manteniendo la estructura del proyecto

// Obtener la ruta del archivo público
$publicIndexPath = __DIR__ . '/../public/index.php';

// Verificar que el archivo existe
if (file_exists($publicIndexPath)) {
    // Incluir el archivo principal
    require_once $publicIndexPath;
} else {
    // Si no existe, devolver error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Archivo principal no encontrado'
    ]);
}

