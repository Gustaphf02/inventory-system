<?php
// Configurar manejo de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

session_start();

try {
    require_once __DIR__ . '/../.auth.php';
    require_once __DIR__ . '/../includes/SystemLogger.php';

    // Solo Admin y Manager pueden ver logs
    requireRole(['admin','manager']);

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Manejar preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_logs':
            $limit = (int)($_GET['limit'] ?? 50);
            $logs = SystemLogger::getRecentLogs($limit);
            
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'count' => count($logs),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get_stats':
            $logs = SystemLogger::getRecentLogs(1000);
            $stats = [
                'total_logs' => count($logs),
                'info_count' => count(array_filter($logs, fn($log) => $log['level'] === 'INFO')),
                'warning_count' => count(array_filter($logs, fn($log) => $log['level'] === 'WARNING')),
                'error_count' => count(array_filter($logs, fn($log) => $log['level'] === 'ERROR')),
                'unique_users' => count(array_unique(array_column($logs, 'user'))),
                'modules' => array_count_values(array_column($logs, 'module'))
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'export':
            $export = SystemLogger::exportToCSV();
            SystemLogger::logUserActivity('EXPORT_LOGS_API', "Exportación de logs via API");
            
            echo json_encode([
                'success' => true,
                'filename' => $export['filename'],
                'size' => $export['size'],
                'download_url' => 'data:text/csv;charset=utf-8,' . urlencode($export['content'])
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?>
