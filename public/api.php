<?php
// API endpoint específico para Render
// Este archivo maneja todas las peticiones API

session_start();

// Configurar manejo de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

try {
    // Incluir el sistema de logging
    require_once __DIR__ . '/includes/SystemLogger.php';
} catch (Exception $e) {
    error_log('Error loading SystemLogger: ' . $e->getMessage());
}

// Headers para CORS y seguridad
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Si hay parámetro 'path' en GET, usarlo
if (isset($_GET['path'])) {
    $apiPath = $_GET['path'];
} else {
    // Si es una petición POST directa a api.php, procesar como products
    if ($path === '/api.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $apiPath = 'products';
    } else {
        // Remover el prefijo /api/ si existe
        if (strpos($path, '/api/') === 0) {
            $apiPath = substr($path, 5); // Remover '/api/'
        } else {
            $apiPath = ltrim($path, '/');
        }
    }
}

error_log("API Request: $path -> $apiPath, Method: " . $_SERVER['REQUEST_METHOD']);

// Datos de ejemplo (mismo que en index.php)
$sampleData = [
    'products' => [
        [
            'id' => 1,
            'sku' => 'RES-1K-1/4W',
            'name' => 'Resistor 1K Ohm 1/4W',
            'description' => 'Resistor de carbón 1K ohmios, potencia 1/4W, tolerancia 5%',
            'brand' => 'Vishay',
            'model' => 'CRCW0805',
            'price' => 0.10,
            'cost' => 0.05,
            'stock_quantity' => 1000,
            'min_stock_level' => 100,
            'max_stock_level' => 2000,
            'category_id' => 3,
            'supplier_id' => 1,
            'category_name' => 'Resistores',
            'supplier_name' => 'Mouser Electronics',
            'serial_number' => 'RES001-2024-001',
            'department' => 'Telemática',
            'location' => 'Almacén A - Estante 1',
            'label' => 'RES-1K-001',
            'barcode' => 'INV-RES-1K-001',
            'expiration_date' => null,
            'status' => 'active',
            'created_at' => '2024-01-15 10:30:00',
            'updated_at' => '2024-01-15 10:30:00'
        ],
        [
            'id' => 2,
            'sku' => 'CAP-100U-16V',
            'name' => 'Capacitor 100µF 16V',
            'description' => 'Capacitor electrolítico 100 microfaradios, voltaje 16V',
            'brand' => 'Panasonic',
            'model' => 'EEA-FC1H101',
            'price' => 0.25,
            'cost' => 0.12,
            'stock_quantity' => 500,
            'min_stock_level' => 50,
            'max_stock_level' => 1000,
            'category_id' => 4,
            'supplier_id' => 2,
            'category_name' => 'Capacitores',
            'supplier_name' => 'Digi-Key Electronics',
            'serial_number' => 'CAP001-2024-001',
            'department' => 'S1',
            'location' => 'Almacén B - Estante 2',
            'label' => 'CAP-100U-001',
            'barcode' => 'INV-CAP-100U-001',
            'expiration_date' => null,
            'status' => 'active',
            'created_at' => '2024-01-15 11:15:00',
            'updated_at' => '2024-01-15 11:15:00'
        ]
    ]
];

// Función helper para logging seguro
function safeLog($level, $module, $action, $details = '') {
    try {
        if (class_exists('SystemLogger')) {
            SystemLogger::logUserActivity($action, $details);
        }
    } catch (Exception $e) {
        error_log('Error logging: ' . $e->getMessage());
    }
}

// Enrutamiento de API
switch ($apiPath) {
    case 'test':
        echo json_encode(['success' => true, 'message' => 'API endpoint working', 'path' => $apiPath, 'method' => $_SERVER['REQUEST_METHOD']]);
        break;
        
    case 'debug':
        echo json_encode([
            'success' => true,
            'debug_info' => [
                'request_uri' => $_SERVER['REQUEST_URI'],
                'path' => $path,
                'api_path' => $apiPath,
                'method' => $_SERVER['REQUEST_METHOD'],
                'query_string' => $_SERVER['QUERY_STRING'] ?? '',
                'headers' => getallheaders(),
                'post_data' => $_POST,
                'raw_input' => file_get_contents('php://input')
            ]
        ]);
        break;
        
    case 'products':
        try {
            error_log("Products API: Processing request - Method: " . $_SERVER['REQUEST_METHOD'] . ", Path: $apiPath");
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de productos");
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Crear nuevo producto
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    error_log("Products POST: Datos JSON inválidos");
                    echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
                    break;
                }
                
                error_log("Products POST: Datos recibidos: " . json_encode($input));
                
                // Validar campos requeridos
                $required = ['sku', 'name', 'serial_number', 'label'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        error_log("Products POST: Campo requerido faltante: $field");
                        echo json_encode(['success' => false, 'error' => "Campo requerido: $field"]);
                        break 2;
                    }
                }
                
                // Validar campos únicos
                foreach ($sampleData['products'] as $existingProduct) {
                    if (strtolower($existingProduct['sku']) === strtolower($input['sku'])) {
                        error_log("Products POST: SKU duplicado: " . $input['sku']);
                        echo json_encode(['success' => false, 'error' => 'El SKU ya existe. Por favor usa un SKU diferente.']);
                        break 2;
                    }
                    
                    if (strtolower($existingProduct['serial_number']) === strtolower($input['serial_number'])) {
                        error_log("Products POST: Serial duplicado: " . $input['serial_number']);
                        echo json_encode(['success' => false, 'error' => 'El número de serial ya existe. Por favor usa un serial diferente.']);
                        break 2;
                    }
                    
                    if (strtolower($existingProduct['label']) === strtolower($input['label'])) {
                        error_log("Products POST: Marbete duplicado: " . $input['label']);
                        echo json_encode(['success' => false, 'error' => 'El marbete ya existe. Por favor usa un marbete diferente.']);
                        break 2;
                    }
                }
                
                // Generar nuevo ID
                $newId = max(array_column($sampleData['products'], 'id')) + 1;
                
                // Crear nuevo producto
                $newProduct = [
                    'id' => $newId,
                    'sku' => $input['sku'],
                    'name' => $input['name'],
                    'description' => $input['description'] ?? '',
                    'brand' => $input['brand'] ?? '',
                    'model' => $input['model'] ?? '',
                    'price' => floatval($input['price'] ?? 0),
                    'cost' => floatval($input['cost'] ?? 0),
                    'stock_quantity' => intval($input['stock_quantity'] ?? 0),
                    'min_stock_level' => intval($input['min_stock_level'] ?? 0),
                    'max_stock_level' => intval($input['max_stock_level'] ?? 0),
                    'category_id' => intval($input['category_id'] ?? 1),
                    'supplier_id' => intval($input['supplier_id'] ?? 1),
                    'category_name' => 'Electrónica',
                    'supplier_name' => 'Mouser Electronics',
                    'serial_number' => $input['serial_number'],
                    'department' => $input['department'] ?? '',
                    'location' => $input['location'] ?? '',
                    'label' => $input['label'] ?? '',
                    'barcode' => $input['barcode'] ?? '',
                    'expiration_date' => $input['expiration_date'] ?? null,
                    'status' => $input['status'] ?? 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Agregar a los datos
                $sampleData['products'][] = $newProduct;
                
                // Log de creación
                safeLog('INFO', 'PRODUCT', 'CREATE', "Producto creado: {$newProduct['sku']} - {$newProduct['name']}");
                error_log("Products POST: Producto creado exitosamente con ID: " . $newProduct['id']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'data' => $newProduct
                ]);
            } else {
                // GET - Listar productos
                echo json_encode([
                    'success' => true,
                    'data' => $sampleData['products'],
                    'total' => count($sampleData['products'])
                ]);
            }
        } catch (Exception $e) {
            error_log("Products API Error: " . $e->getMessage());
            error_log("Products API Error Trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'reports/dashboard/stats':
        // Estadísticas del dashboard
        $totalProducts = count($sampleData['products']);
        $totalValue = array_sum(array_column($sampleData['products'], 'price'));
        $lowStockCount = count(array_filter($sampleData['products'], function($product) {
            return $product['stock_quantity'] <= $product['min_stock_level'];
        }));
        
        echo json_encode([
            'success' => true,
            'data' => [
                'totalProducts' => $totalProducts,
                'totalValue' => $totalValue,
                'lowStockCount' => $lowStockCount,
                'totalSuppliers' => 4,
                'avgStockPerProduct' => $totalProducts > 0 ? array_sum(array_column($sampleData['products'], 'stock_quantity')) / $totalProducts : 0
            ]
        ]);
        break;
        
    case 'departments':
        // Lista de departamentos
        $departments = [
            ['id' => 1, 'name' => 'Telemática', 'description' => 'Departamento de Telemática'],
            ['id' => 2, 'name' => 'S1', 'description' => 'Departamento S1'],
            ['id' => 3, 'name' => 'Protección', 'description' => 'Departamento de Protección'],
            ['id' => 4, 'name' => 'S3', 'description' => 'Departamento S3']
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $departments,
            'total' => count($departments)
        ]);
        break;
        
    case 'locations':
        // Lista de ubicaciones
        $locations = [
            ['id' => 1, 'name' => 'Almacén A - Estante 1', 'description' => 'Almacén principal'],
            ['id' => 2, 'name' => 'Almacén B - Estante 2', 'description' => 'Almacén secundario'],
            ['id' => 3, 'name' => 'Oficina - Escritorio 1', 'description' => 'Oficina principal'],
            ['id' => 4, 'name' => 'Laboratorio - Mesa 1', 'description' => 'Laboratorio de pruebas']
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $locations,
            'total' => count($locations)
        ]);
        break;
        
    case 'api.php':
        // Si se accede directamente a api.php sin parámetros, mostrar información
        echo json_encode([
            'success' => true,
            'message' => 'API endpoint funcionando',
            'available_endpoints' => ['products', 'test', 'debug', 'reports/dashboard/stats', 'departments', 'locations'],
            'usage' => 'Use POST /api.php para crear productos o GET /api.php?path=products para listar'
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Endpoint no encontrado',
            'path' => $apiPath,
            'available_endpoints' => ['products', 'test', 'debug', 'reports/dashboard/stats', 'departments', 'locations']
        ]);
        break;
}
?>