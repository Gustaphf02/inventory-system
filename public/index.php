<?php
session_start();

// Configurar manejo de errores ANTES de incluir otros archivos
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

try {
    // Incluir el sistema de logging
    require_once __DIR__ . '/includes/SystemLogger.php';
} catch (Exception $e) {
    // Si hay error con SystemLogger, continuar sin logging
    error_log('Error loading SystemLogger: ' . $e->getMessage());
}

/**
 * Sistema de Inventario - Versión Simplificada sin Base de Datos
 * Funciona completamente con datos de ejemplo
 * Compatible con Render.com y otros servicios de hosting
 */

// Detectar si estamos en producción (Render)
$isProduction = getenv('APP_ENV') === 'production' || getenv('RENDER');

// Headers para CORS y seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($isProduction) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Datos de ejemplo en memoria
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
            'serial_number' => 'RES001-2025-001',
            'department' => 'Electrónica',
            'location' => 'Almacén A - Estante 1',
            'label' => 'RES-1K-001',
            'barcode' => 'INV-RES-1K-001',
            'expiration_date' => null,
            'status' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 2,
            'sku' => 'CAP-100NF-50V',
            'name' => 'Capacitor Cerámico 100nF',
            'description' => 'Capacitor cerámico 100nF, 50V, X7R',
            'brand' => 'Murata',
            'model' => 'GRM188R71H104KA01D',
            'price' => 0.15,
            'cost' => 0.08,
            'stock_quantity' => 500,
            'min_stock_level' => 50,
            'category_id' => 4,
            'supplier_id' => 1,
            'category_name' => 'Capacitores',
            'supplier_name' => 'Mouser Electronics',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 3,
            'sku' => 'LED-RED-5MM',
            'name' => 'LED Rojo 5mm',
            'description' => 'LED rojo 5mm, 20mA, 2.1V',
            'brand' => 'Kingbright',
            'model' => 'L-7113HD',
            'price' => 0.25,
            'cost' => 0.12,
            'stock_quantity' => 200,
            'min_stock_level' => 25,
            'category_id' => 2,
            'supplier_id' => 2,
            'category_name' => 'Semiconductores',
            'supplier_name' => 'DigiKey',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 4,
            'sku' => 'TRANS-BC547',
            'name' => 'Transistor NPN BC547',
            'description' => 'Transistor NPN BC547, 45V, 100mA',
            'brand' => 'ON Semiconductor',
            'model' => 'BC547B',
            'price' => 0.30,
            'cost' => 0.15,
            'stock_quantity' => 150,
            'min_stock_level' => 20,
            'category_id' => 2,
            'supplier_id' => 2,
            'category_name' => 'Semiconductores',
            'supplier_name' => 'DigiKey',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 5,
            'sku' => 'CONN-USB-A',
            'name' => 'Conector USB-A',
            'description' => 'Conector USB-A hembra, montaje PCB',
            'brand' => 'Amphenol',
            'model' => 'USB-A-S-RA',
            'price' => 1.50,
            'cost' => 0.75,
            'stock_quantity' => 75,
            'min_stock_level' => 10,
            'category_id' => 5,
            'supplier_id' => 3,
            'category_name' => 'Conectores',
            'supplier_name' => 'Newark',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 6,
            'sku' => 'CABLE-JUMPER-20CM',
            'name' => 'Cable Jumper 20cm',
            'description' => 'Cable jumper macho-macho, 20cm',
            'price' => 0.50,
            'cost' => 0.25,
            'stock_quantity' => 300,
            'min_stock_level' => 50,
            'category_id' => 7,
            'supplier_id' => 3,
            'category_name' => 'Cables',
            'supplier_name' => 'Newark',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 7,
            'sku' => 'PCB-5X7CM',
            'name' => 'Placa PCB 5x7cm',
            'description' => 'Placa PCB perforada 5x7cm, 2.54mm pitch',
            'price' => 2.00,
            'cost' => 1.00,
            'stock_quantity' => 50,
            'min_stock_level' => 5,
            'category_id' => 8,
            'supplier_id' => 4,
            'category_name' => 'Placas PCB',
            'supplier_name' => 'RS Components',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 8,
            'sku' => 'TOOL-MULTIMETER',
            'name' => 'Multímetro Digital',
            'description' => 'Multímetro digital básico, 3.5 dígitos',
            'price' => 25.00,
            'cost' => 12.50,
            'stock_quantity' => 10,
            'min_stock_level' => 2,
            'category_id' => 6,
            'supplier_id' => 4,
            'category_name' => 'Herramientas',
            'supplier_name' => 'RS Components',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ]
    ],
    'categories' => [
        ['id' => 1, 'name' => 'Electrónicos', 'description' => 'Componentes electrónicos y dispositivos', 'product_count' => 8],
        ['id' => 2, 'name' => 'Semiconductores', 'description' => 'Transistores, diodos, circuitos integrados', 'product_count' => 2],
        ['id' => 3, 'name' => 'Resistores', 'description' => 'Resistores de varios tipos y valores', 'product_count' => 1],
        ['id' => 4, 'name' => 'Capacitores', 'description' => 'Capacitores cerámicos, electrolíticos, etc.', 'product_count' => 1],
        ['id' => 5, 'name' => 'Conectores', 'description' => 'Conectores y terminales', 'product_count' => 1],
        ['id' => 6, 'name' => 'Herramientas', 'description' => 'Herramientas de electrónica', 'product_count' => 1],
        ['id' => 7, 'name' => 'Cables', 'description' => 'Cables y alambres', 'product_count' => 1],
        ['id' => 8, 'name' => 'Placas PCB', 'description' => 'Placas de circuito impreso', 'product_count' => 1]
    ],
    'suppliers' => [
        ['id' => 1, 'name' => 'Mouser Electronics', 'contact_person' => 'John Smith', 'email' => 'sales@mouser.com', 'phone' => '+1-800-346-6873', 'product_count' => 2],
        ['id' => 2, 'name' => 'DigiKey', 'contact_person' => 'Jane Doe', 'email' => 'sales@digikey.com', 'phone' => '+1-800-344-4539', 'product_count' => 2],
        ['id' => 3, 'name' => 'Newark', 'contact_person' => 'Bob Johnson', 'email' => 'sales@newark.com', 'phone' => '+1-800-463-9275', 'product_count' => 2],
        ['id' => 4, 'name' => 'RS Components', 'contact_person' => 'Alice Brown', 'email' => 'sales@rs-components.com', 'phone' => '+44-800-240-240', 'product_count' => 2]
    ],
    
    'departments' => [
        ['id' => 1, 'name' => 'Telemática', 'description' => 'Sistemas de telecomunicaciones y redes', 'location' => 'Almacén A', 'manager' => 'Carlos López'],
        ['id' => 2, 'name' => 'S1', 'description' => 'Sistema de seguridad nivel 1', 'location' => 'Almacén B', 'manager' => 'María García'],
        ['id' => 3, 'name' => 'Protección', 'description' => 'Sistemas de protección y seguridad', 'location' => 'Almacén C', 'manager' => 'Juan Pérez'],
        ['id' => 4, 'name' => 'S3', 'description' => 'Sistema de seguridad nivel 3', 'location' => 'Almacén D', 'manager' => 'Ana Martínez']
    ],
    
    'locations' => [
        ['id' => 1, 'name' => 'Almacén A', 'description' => 'Almacén principal de electrónica', 'address' => 'Calle Principal 123', 'capacity' => 1000],
        ['id' => 2, 'name' => 'Almacén B', 'description' => 'Almacén de iluminación', 'address' => 'Calle Secundaria 456', 'capacity' => 500],
        ['id' => 3, 'name' => 'Almacén C', 'description' => 'Almacén de prototipos', 'address' => 'Calle Terciaria 789', 'capacity' => 300],
        ['id' => 4, 'name' => 'Almacén D', 'description' => 'Almacén mecánico', 'address' => 'Calle Cuarta 101', 'capacity' => 800],
        ['id' => 5, 'name' => 'Almacén E', 'description' => 'Almacén de cables', 'address' => 'Calle Quinta 202', 'capacity' => 600]
    ]
];

// Función helper para logging seguro
function safeLog($level, $module, $action, $details = '') {
    try {
        if (class_exists('SystemLogger')) {
            SystemLogger::logUserActivity($action, $details);
        }
    } catch (Exception $e) {
        // Si hay error con logging, continuar sin logging
        error_log('Error logging: ' . $e->getMessage());
    }
}

// Función para calcular estadísticas
function calculateStats($products) {
    $totalProducts = count($products);
    $totalValue = array_sum(array_map(function($p) { return $p['stock_quantity'] * $p['cost']; }, $products));
    $lowStockProducts = count(array_filter($products, function($p) { return $p['stock_quantity'] <= $p['min_stock_level']; }));
    
    return [
        'totalProducts' => $totalProducts,
        'totalValue' => $totalValue,
        'lowStockProducts' => $lowStockProducts,
        'totalSuppliers' => 4,
        'avgStockPerProduct' => $totalProducts > 0 ? array_sum(array_column($products, 'stock_quantity')) / $totalProducts : 0
    ];
}

// Obtener la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Si es una petición API, manejar como JSON
if (strpos($path, '/api/') === 0 || in_array($path, ['/auth/me', '/products', '/categories', '/suppliers', '/departments', '/locations', '/inventory/summary', '/reports/dashboard/stats', '/reports/inventory/summary', '/reports/inventory/low-stock', '/health', '/test'])) {
    // Debug: Log de la ruta detectada
    error_log("API Route detected: $path, Method: " . $_SERVER['REQUEST_METHOD']);
    try {
        // Configurar headers para JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Determinar la ruta de la API
        if (strpos($path, '/api/') === 0) {
            $apiPath = substr($path, 4);
        } else {
            $apiPath = ltrim($path, '/');
        }
        
        // Enrutamiento de API
        switch ($apiPath) {
        case 'test':
            echo json_encode(['success' => true, 'message' => 'Test endpoint working']);
            break;
            
        case 'api/products':
        case 'products':
            try {
                // Log del acceso a productos
                safeLog('INFO', 'API', 'API_ACCESS', "Consulta de productos");
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Crear nuevo producto
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
                    break;
                }
                
                // Validar campos requeridos
                $required = ['sku', 'name', 'serial_number'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        echo json_encode(['success' => false, 'error' => "Campo requerido: $field"]);
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
                echo json_encode([
                    'success' => false,
                    'error' => 'Error interno del servidor: ' . $e->getMessage()
                ]);
            }
            break;
            
        case 'auth/me':
            if (isset($_SESSION['user'])) {
                echo json_encode([
                    'authenticated' => true,
                    'user' => $_SESSION['user']
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'authenticated' => false,
                    'message' => 'Not authenticated'
                ]);
            }
            break;
        case 'health':
            echo json_encode([
                'status' => 'ok',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0',
                'mode' => $isProduction ? 'production' : 'demo',
                'environment' => getenv('APP_ENV') ?: 'development',
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server',
                'php_version' => PHP_VERSION,
                'render' => getenv('RENDER') ? 'true' : 'false'
            ]);
            break;
            
        case 'categories':
            // Log del acceso a categorías
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de categorías");
            echo json_encode([
                'success' => true,
                'data' => $sampleData['categories']
            ]);
            break;
            
        case 'suppliers':
            // Log del acceso a proveedores
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de proveedores");
            echo json_encode([
                'success' => true,
                'data' => $sampleData['suppliers']
            ]);
            break;
            
        case 'departments':
            // Log del acceso a departamentos
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de departamentos");
            echo json_encode([
                'success' => true,
                'data' => $sampleData['departments']
            ]);
            break;
            
        case 'locations':
            // Log del acceso a ubicaciones
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de ubicaciones");
            echo json_encode([
                'success' => true,
                'data' => $sampleData['locations']
            ]);
            break;
            
        case 'inventory/summary':
            // Resumen completo del inventario
            $inventorySummary = [
                'total_products' => count($sampleData['products']),
                'total_value' => array_sum(array_map(function($p) { return $p['stock_quantity'] * $p['cost']; }, $sampleData['products'])),
                'departments' => [],
                'locations' => [],
                'low_stock_products' => array_filter($sampleData['products'], function($p) { return $p['stock_quantity'] <= $p['min_stock_level']; })
            ];
            
            // Agrupar por departamento
            foreach ($sampleData['products'] as $product) {
                $dept = $product['department'];
                if (!isset($inventorySummary['departments'][$dept])) {
                    $inventorySummary['departments'][$dept] = [
                        'name' => $dept,
                        'product_count' => 0,
                        'total_value' => 0,
                        'products' => []
                    ];
                }
                $inventorySummary['departments'][$dept]['product_count']++;
                $inventorySummary['departments'][$dept]['total_value'] += $product['stock_quantity'] * $product['cost'];
                $inventorySummary['departments'][$dept]['products'][] = $product;
            }
            
            // Agrupar por ubicación
            foreach ($sampleData['products'] as $product) {
                $location = explode(' - ', $product['location'])[0]; // Solo el nombre del almacén
                if (!isset($inventorySummary['locations'][$location])) {
                    $inventorySummary['locations'][$location] = [
                        'name' => $location,
                        'product_count' => 0,
                        'total_value' => 0,
                        'products' => []
                    ];
                }
                $inventorySummary['locations'][$location]['product_count']++;
                $inventorySummary['locations'][$location]['total_value'] += $product['stock_quantity'] * $product['cost'];
                $inventorySummary['locations'][$location]['products'][] = $product;
            }
            
            safeLog('INFO', 'API', 'API_ACCESS', "Consulta de resumen de inventario");
            echo json_encode([
                'success' => true,
                'data' => $inventorySummary
            ]);
            break;
            
        case 'reports/dashboard/stats':
            $stats = calculateStats($sampleData['products']);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'reports/inventory/summary':
            $stats = calculateStats($sampleData['products']);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'reports/inventory/low-stock':
            $lowStockProducts = array_filter($sampleData['products'], function($p) { 
                return $p['stock_quantity'] <= $p['min_stock_level']; 
            });
            echo json_encode([
                'success' => true,
                'data' => array_values($lowStockProducts)
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint no encontrado: ' . $apiPath
            ]);
            break;
    }
    } catch (Exception $e) {
        // Log del error
        error_log("API Error: " . $e->getMessage());
        error_log("API Error Trace: " . $e->getTraceAsString());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor',
            'error' => $e->getMessage()
        ]);
    }
} else {
    // Si no es API
    $pathOnly = trim($path, '/');
    
    // Si no hay sesión activa, redirigir al login
    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
    
    // Si hay sesión activa, mostrar el sistema principal
    // Log del acceso al sistema
    safeLog('INFO', 'SYSTEM', 'SYSTEM_ACCESS', "Acceso al sistema principal");
    include 'index.html';
}
?>
