<?php
// Configurar headers JSON para todas las respuestas API
header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir middleware de autenticación ultra simplificado
require_once __DIR__ . '/.auth-clean.php';

// Configurar manejo de errores ANTES de incluir otros archivos
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

try {
    // Obtener la ruta de la API
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);

    // Si es una petición API, manejar como JSON
    if (strpos($path, '/api/') === 0 || in_array($path, ['/auth/me', '/products', '/categories', '/suppliers', '/departments', '/locations', '/inventory/summary', '/reports/dashboard/stats', '/health', '/test'])) {
        
        // Configurar headers para JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Determinar la ruta de la API
        if (strpos($path, '/api/') === 0) {
            $apiPath = substr($path, 4);
        } else {
            $apiPath = ltrim($path, '/');
        }
        
        // Enrutamiento de API
        switch ($apiPath) {
            case 'auth/me':
                // Verificar si hay una sesión activa
                if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
                    // Usuario autenticado
                    $user = $_SESSION['user'];
                    echo json_encode([
                        'success' => true,
                        'data' => $user,
                        'authenticated' => true
                    ]);
                } else {
                    // No hay sesión activa
                    echo json_encode([
                        'success' => false,
                        'error' => 'No hay sesión activa',
                        'authenticated' => false,
                        'data' => null
                    ]);
                }
                break;
                
            case 'reports/dashboard/stats':
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'totalProducts' => 0,
                        'totalValue' => 0,
                        'lowStockProducts' => 0,
                        'totalSuppliers' => 4
                    ]
                ]);
                break;
                
            case 'departments':
                echo json_encode([
                    'success' => true,
                    'data' => [
                        ['id' => 1, 'name' => 'Telemática'],
                        ['id' => 2, 'name' => 'S1'],
                        ['id' => 3, 'name' => 'Protección'],
                        ['id' => 4, 'name' => 'S3']
                    ]
                ]);
                break;
                
            case 'locations':
                echo json_encode([
                    'success' => true,
                    'data' => [
                        ['id' => 1, 'name' => 'Almacén Principal'],
                        ['id' => 2, 'name' => 'Almacén Secundario'],
                        ['id' => 3, 'name' => 'Laboratorio'],
                        ['id' => 4, 'name' => 'Oficina']
                    ]
                ]);
                break;
                
            case 'categories':
                echo json_encode([
                    'success' => true,
                    'data' => [
                        ['id' => 1, 'name' => 'Electrónica', 'description' => 'Componentes electrónicos', 'product_count' => 0],
                        ['id' => 2, 'name' => 'Iluminación', 'description' => 'Sistemas de iluminación', 'product_count' => 0],
                        ['id' => 3, 'name' => 'Resistores', 'description' => 'Resistencias eléctricas', 'product_count' => 0],
                        ['id' => 4, 'name' => 'Capacitores', 'description' => 'Capacitores eléctricos', 'product_count' => 0],
                        ['id' => 5, 'name' => 'Cables', 'description' => 'Cables y conectores', 'product_count' => 0]
                    ]
                ]);
                break;
                
            case 'suppliers':
                echo json_encode([
                    'success' => true,
                    'data' => [
                        ['id' => 1, 'name' => 'Mouser Electronics', 'contact_person' => 'John Smith', 'email' => 'contact@mouser.com', 'phone' => '+1-555-0123', 'product_count' => 0],
                        ['id' => 2, 'name' => 'Digi-Key Electronics', 'contact_person' => 'Jane Doe', 'email' => 'contact@digikey.com', 'phone' => '+1-555-0124', 'product_count' => 0],
                        ['id' => 3, 'name' => 'Farnell', 'contact_person' => 'Bob Johnson', 'email' => 'contact@farnell.com', 'phone' => '+1-555-0125', 'product_count' => 0],
                        ['id' => 4, 'name' => 'RS Components', 'contact_person' => 'Alice Brown', 'email' => 'contact@rs-components.com', 'phone' => '+1-555-0126', 'product_count' => 0]
                    ]
                ]);
                break;
                
            case 'products':
                echo json_encode([
                    'success' => true,
                    'data' => []
                ]);
                break;
                
            case 'health':
                echo json_encode([
                    'success' => true,
                    'status' => 'healthy',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'php_version' => phpversion()
                ]);
                break;
                
            case 'test':
                echo json_encode([
                    'success' => true,
                    'message' => 'API funcionando correctamente',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                break;
                
            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Endpoint no encontrado',
                    'path' => $apiPath
                ]);
                break;
        }
    } else {
        // Si no es una petición API, mostrar la página HTML
        include 'index.html';
    }

} catch (Exception $e) {
    // Manejo global de errores - siempre devolver JSON para APIs
    error_log("Global Error: " . $e->getMessage());
    error_log("Global Error Trace: " . $e->getTraceAsString());
    
    // Si es una llamada API, devolver JSON de error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0 || 
        in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ['/auth/me', '/products', '/categories', '/suppliers', '/departments', '/locations', '/inventory/summary', '/reports/dashboard/stats'])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor',
            'message' => 'Ha ocurrido un error inesperado'
        ]);
    } else {
        // Para páginas normales, mostrar error HTML
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body><h1>Error del Servidor</h1><p>Ha ocurrido un error inesperado.</p></body></html>';
    }
}
?>