<?php
// Archivo de prueba para la API de logs
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

session_start();

header('Content-Type: application/json');

try {
    // Simular usuario admin para pruebas
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = [
            'name' => 'Usuario Demo',
            'role' => 'admin',
            'email' => 'demo@inventory.com'
        ];
    }
    
    $action = $_GET['action'] ?? 'get_logs';
    
    switch ($action) {
        case 'get_logs':
            // Logs de ejemplo
            $logs = [
                [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'level' => 'INFO',
                    'module' => 'SYSTEM',
                    'user' => 'admin',
                    'action' => 'SYSTEM_ACCESS',
                    'ip' => '127.0.0.1',
                    'details' => 'Acceso al sistema principal'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
                    'level' => 'INFO',
                    'module' => 'AUTH',
                    'user' => 'admin',
                    'action' => 'LOGIN_SUCCESS',
                    'ip' => '127.0.0.1',
                    'details' => 'Login exitoso'
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'count' => count($logs),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
