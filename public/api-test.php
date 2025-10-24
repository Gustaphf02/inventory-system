<?php
// Test simple para verificar que api.php funciona
header('Content-Type: application/json');

try {
    echo json_encode([
        'success' => true,
        'message' => 'API test funcionando',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en test: ' . $e->getMessage()
    ]);
}
?>
