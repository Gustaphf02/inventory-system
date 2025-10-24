<?php
// Configurar headers JSON para todas las respuestas API
header('Content-Type: application/json; charset=utf-8');

session_start();

// Incluir middleware de autenticación
require_once __DIR__ . '/.auth.php';

// Configurar manejo de errores ANTES de incluir otros archivos
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

try {
    // Incluir el sistema de logging de forma segura
    if (file_exists(__DIR__ . '/includes/SystemLogger.php')) {
        require_once __DIR__ . '/includes/SystemLogger.php';
        $systemLoggerAvailable = true;
    } else {
        $systemLoggerAvailable = false;
        error_log('SystemLogger.php not found, continuing without advanced logging');
    }
} catch (Exception $e) {
    // Si hay error con SystemLogger, continuar sin logging
    $systemLoggerAvailable = false;
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

// Función para cargar productos desde archivo
function loadProductsFromFile() {
    $file = __DIR__ . '/data/products.json';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        return $data ? $data : [];
    }
    return [];
}

// Función para guardar productos en archivo
function saveProductsToFile($products) {
    $file = __DIR__ . '/data/products.json';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT));
}

// Cargar productos guardados
$savedProducts = loadProductsFromFile();

// Datos de ejemplo en memoria - CARGAR DESDE ARCHIVO
$sampleData = [
    'products' => $savedProducts,
    'categories' => [
        ['id' => 1, 'name' => 'Electrónica', 'description' => 'Componentes electrónicos'],
        ['id' => 2, 'name' => 'Iluminación', 'description' => 'Productos de iluminación'],
        ['id' => 3, 'name' => 'Resistores', 'description' => 'Resistores y resistencias'],
        ['id' => 4, 'name' => 'Capacitores', 'description' => 'Capacitores y condensadores'],
        ['id' => 5, 'name' => 'Cables', 'description' => 'Cables y conectores']
    ],
    'suppliers' => [
        ['id' => 1, 'name' => 'Mouser Electronics', 'contact' => 'John Smith', 'email' => 'john@mouser.com', 'phone' => '+1-555-0123'],
        ['id' => 2, 'name' => 'Digi-Key Electronics', 'contact' => 'Sarah Johnson', 'email' => 'sarah@digikey.com', 'phone' => '+1-555-0456'],
        ['id' => 3, 'name' => 'Farnell', 'contact' => 'Mike Brown', 'email' => 'mike@farnell.com', 'phone' => '+1-555-0789'],
        ['id' => 4, 'name' => 'RS Components', 'contact' => 'Lisa Davis', 'email' => 'lisa@rs-components.com', 'phone' => '+1-555-0321']
    ],
    'departments' => [
        ['id' => 1, 'name' => 'Telemática', 'description' => 'Departamento de Telemática'],
        ['id' => 2, 'name' => 'S1', 'description' => 'Departamento S1'],
        ['id' => 3, 'name' => 'Protección', 'description' => 'Departamento de Protección'],
        ['id' => 4, 'name' => 'S3', 'description' => 'Departamento S3']
    ],
    'locations' => [
        ['id' => 1, 'name' => 'Almacén A', 'description' => 'Almacén principal', 'address' => 'Calle Primera 100', 'capacity' => 1000],
        ['id' => 2, 'name' => 'Almacén B', 'description' => 'Almacén secundario', 'address' => 'Calle Segunda 150', 'capacity' => 800],
        ['id' => 3, 'name' => 'Almacén C', 'description' => 'Almacén de electrónicos', 'address' => 'Calle Tercera 200', 'capacity' => 600],
        ['id' => 4, 'name' => 'Almacén D', 'description' => 'Almacén de cables', 'address' => 'Calle Cuarta 250', 'capacity' => 400],
        ['id' => 5, 'name' => 'Almacén E', 'description' => 'Almacén de cables', 'address' => 'Calle Quinta 202', 'capacity' => 600]
    ]
];

// Función helper para logging seguro
function safeLog($level, $module, $action, $details = '') {
    global $systemLoggerAvailable;
    try {
        if ($systemLoggerAvailable && class_exists('SystemLogger')) {
            SystemLogger::logUserActivity($action, $details);
        } else {
            // Fallback a error_log simple
            error_log("[$level] $module - $action: $details");
        }
    } catch (Exception $e) {
        error_log('Error logging: ' . $e->getMessage());
    }
}

try {
// Obtener la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Si es una petición API, manejar como JSON
if (strpos($path, '/api/') === 0 || in_array($path, ['/auth/me', '/products', '/categories', '/suppliers', '/departments', '/locations', '/inventory/summary', '/reports/dashboard/stats', '/reports/inventory/summary', '/reports/inventory/low-stock', '/health', '/test'])) {
        // Debug: Log de la ruta detectada
        error_log("API Route detected: $path, Method: " . $_SERVER['REQUEST_METHOD']);
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
            
        case 'inventory/summary':
            // Resumen del inventario
            $totalProducts = count($sampleData['products']);
            $totalValue = 0;
            $lowStockCount = 0;
            
            foreach ($sampleData['products'] as $product) {
                $totalValue += floatval($product['price']) * intval($product['stock_quantity']);
                if ($product['stock_quantity'] <= $product['min_stock_level']) {
                    $lowStockCount++;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'totalProducts' => $totalProducts,
                    'totalValue' => $totalValue,
                    'lowStockCount' => $lowStockCount,
                    'totalSuppliers' => count($sampleData['suppliers']),
                    'avgStockPerProduct' => $totalProducts > 0 ? $totalValue / $totalProducts : 0
                ]
            ]);
            break;
            
        case 'test':
            echo json_encode(['success' => true, 'message' => 'Test endpoint working']);
            break;
            
        case 'products':
            try {
                // Log del acceso a productos
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
                $newId = empty($sampleData['products']) ? 1 : max(array_column($sampleData['products'], 'id')) + 1;
                
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
                
                // Guardar en archivo
                saveProductsToFile($sampleData['products']);
                
                // Log de creación
                safeLog('INFO', 'PRODUCT', 'CREATE', "Producto creado: {$newProduct['sku']} - {$newProduct['name']}");
                error_log("Products POST: Producto creado exitosamente con ID: " . $newProduct['id']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'data' => $newProduct
                ]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                // Editar producto existente
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    error_log("Products PUT: Datos JSON inválidos");
                    echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
                    break;
                }
                
                $productId = intval($input['id'] ?? 0);
                if (!$productId) {
                    error_log("Products PUT: ID de producto faltante");
                    echo json_encode(['success' => false, 'error' => 'ID de producto requerido']);
                    break;
                }
                
                // Buscar el producto
                $productIndex = -1;
                foreach ($sampleData['products'] as $index => $product) {
                    if ($product['id'] == $productId) {
                        $productIndex = $index;
                        break;
                    }
                }
                
                if ($productIndex === -1) {
                    error_log("Products PUT: Producto no encontrado con ID: $productId");
                    echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                    break;
                }
                
                // Validar campos únicos (excluyendo el producto actual)
                foreach ($sampleData['products'] as $index => $existingProduct) {
                    if ($index === $productIndex) continue; // Saltar el producto actual
                    
                    if (strtolower($existingProduct['serial_number']) === strtolower($input['serial_number'])) {
                        error_log("Products PUT: Serial duplicado: " . $input['serial_number']);
                        echo json_encode(['success' => false, 'error' => 'El número de serial ya existe. Por favor usa un serial diferente.']);
                        break 2;
                    }
                    
                    if (strtolower($existingProduct['label']) === strtolower($input['label'])) {
                        error_log("Products PUT: Marbete duplicado: " . $input['label']);
                        echo json_encode(['success' => false, 'error' => 'El marbete ya existe. Por favor usa un marbete diferente.']);
                        break 2;
                    }
                }
                
                // Actualizar el producto
                $sampleData['products'][$productIndex] = array_merge($sampleData['products'][$productIndex], [
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
                    'serial_number' => $input['serial_number'],
                    'department' => $input['department'] ?? '',
                    'location' => $input['location'] ?? '',
                    'label' => $input['label'] ?? '',
                    'barcode' => $input['barcode'] ?? '',
                    'expiration_date' => $input['expiration_date'] ?? null,
                    'status' => $input['status'] ?? 'active',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Guardar en archivo
                saveProductsToFile($sampleData['products']);
                
                // Log de actualización
                safeLog('INFO', 'PRODUCT', 'UPDATE', "Producto actualizado: {$sampleData['products'][$productIndex]['sku']} - {$sampleData['products'][$productIndex]['name']}");
                error_log("Products PUT: Producto actualizado exitosamente con ID: $productId");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'data' => $sampleData['products'][$productIndex]
                ]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                // Eliminar producto
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    error_log("Products DELETE: Datos JSON inválidos");
                    echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
                    break;
                }
                
                $productId = intval($input['id'] ?? 0);
                if (!$productId) {
                    error_log("Products DELETE: ID de producto faltante");
                    echo json_encode(['success' => false, 'error' => 'ID de producto requerido']);
                    break;
                }
                
                // Buscar el producto
                $productIndex = -1;
                foreach ($sampleData['products'] as $index => $product) {
                    if ($product['id'] == $productId) {
                        $productIndex = $index;
                        break;
                    }
                }
                
                if ($productIndex === -1) {
                    error_log("Products DELETE: Producto no encontrado con ID: $productId");
                    echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                    break;
                }
                
                // Eliminar el producto
                $deletedProduct = $sampleData['products'][$productIndex];
                unset($sampleData['products'][$productIndex]);
                $sampleData['products'] = array_values($sampleData['products']); // Reindexar array
                
                // Guardar en archivo
                saveProductsToFile($sampleData['products']);
                
                // Log de eliminación
                safeLog('INFO', 'PRODUCT', 'DELETE', "Producto eliminado: {$deletedProduct['sku']} - {$deletedProduct['name']}");
                error_log("Products DELETE: Producto eliminado exitosamente con ID: $productId");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente',
                    'data' => $deletedProduct
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
        
        case 'categories':
            echo json_encode([
                'success' => true,
                'data' => $sampleData['categories'],
                'total' => count($sampleData['categories'])
            ]);
            break;
            
        case 'suppliers':
            echo json_encode([
                'success' => true,
                'data' => $sampleData['suppliers'],
                'total' => count($sampleData['suppliers'])
            ]);
            break;
            
        case 'departments':
            echo json_encode([
                'success' => true,
                'data' => $sampleData['departments'],
                'total' => count($sampleData['departments'])
            ]);
            break;
            
        case 'locations':
            echo json_encode([
                'success' => true,
                'data' => $sampleData['locations'],
                'total' => count($sampleData['locations'])
            ]);
            break;
            
        case 'reports/dashboard/stats':
            $totalProducts = count($sampleData['products']);
            $totalValue = 0;
            $lowStockCount = 0;
            
            foreach ($sampleData['products'] as $product) {
                $totalValue += floatval($product['price']);
                if ($product['stock_quantity'] <= $product['min_stock_level']) {
                    $lowStockCount++;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'totalProducts' => $totalProducts,
                    'totalValue' => $totalValue,
                    'lowStockCount' => $lowStockCount,
                    'totalSuppliers' => count($sampleData['suppliers']),
                    'avgStockPerProduct' => $totalProducts > 0 ? $totalValue / $totalProducts : 0
                ]
            ]);
            break;
            
        case 'health':
            echo json_encode([
                'success' => true,
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
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
    // Si no es API, mostrar la página principal
    // Verificar sesión
    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
    
    // Log del acceso al sistema
    safeLog('INFO', 'USER', 'SYSTEM_ACCESS', "Acceso al sistema principal");
    
    // Incluir la página principal
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
