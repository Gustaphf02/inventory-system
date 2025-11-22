
<?php
// Configurar manejo de errores ANTES de cualquier output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0);

session_start();

// Incluir middleware de autenticación limpio
require_once __DIR__ . '/.auth.php';

try {
    // Incluir DatabaseManager con fallback automático
    require_once __DIR__ . '/DatabaseManager.php';
    
    // Incluir el sistema de logging de forma segura
    if (file_exists(__DIR__ . '/includes/SystemLogger.php')) {
        require_once __DIR__ . '/includes/SystemLogger.php';
        $systemLoggerAvailable = true;
    } else {
        $systemLoggerAvailable = false;
    }
    
    // Función de logging segura
    function safeLog($level, $module, $action, $message) {
        global $systemLoggerAvailable;
        if ($systemLoggerAvailable) {
            try {
                SystemLogger::log($level, $module, $action, $message);
            } catch (Exception $e) {
                error_log("Logging error: " . $e->getMessage());
            }
        } else {
            error_log("[$level] $module:$action - $message");
        }
    }
    
    // Obtener instancia del DatabaseManager
    $db = DatabaseManager::getInstance();

// Endpoints de prueba
if (isset($_GET['test'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['test']) {
        case 'mongodb':
            try {
                // Verificar si MongoDB está configurado
                $mongoUri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');
                
                if (!$mongoUri) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'MONGODB_URI no configurado',
                        'mongodb_available' => false
                    ]);
                    break;
                }
                
                // Verificar si la extensión MongoDB está instalada
                if (!extension_loaded('mongodb')) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Extensión MongoDB no instalada',
                        'mongodb_available' => false
                    ]);
                    break;
                }
                
                // Verificar si la clase MongoDB\Client existe
                if (!class_exists('MongoDB\Client')) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Clase MongoDB\\Client no encontrada',
                        'mongodb_available' => false
                    ]);
                    break;
                }
                
                // Intentar conectar
                $client = new MongoDB\Client($mongoUri);
                $database = $client->selectDatabase('inventory_db');
                $collection = $database->selectCollection('products');
                
                // Probar conexión
                $result = $collection->countDocuments();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'MongoDB Atlas funcionando correctamente',
                    'mongodb_available' => true,
                    'products_count' => $result,
                    'connection_string' => substr($mongoUri, 0, 30) . '...'
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error de conexión: ' . $e->getMessage(),
                    'mongodb_available' => false
                ]);
            }
            break;
            
        case 'status':
            echo json_encode([
                'php_version' => phpversion(),
                'extensions' => [
                    'mongodb' => extension_loaded('mongodb'),
                    'json' => extension_loaded('json'),
                    'mbstring' => extension_loaded('mbstring'),
                    'openssl' => extension_loaded('openssl'),
                    'curl' => extension_loaded('curl')
                ],
                'environment' => [
                    'mongodb_uri_configured' => !empty($_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI')),
                    'mongodb_uri_preview' => $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI') ? substr($_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI'), 0, 30) . '...' : 'Not configured'
                ],
                'classes' => [
                    'mongodb_client' => class_exists('MongoDB\Client'),
                    'mongodb_objectid' => class_exists('MongoDB\BSON\ObjectId')
                ]
            ], JSON_PRETTY_PRINT);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Test endpoint no válido',
                'available_tests' => ['mongodb', 'status']
            ]);
    }
    exit;
}

// Obtener la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Iniciar sesión solo si no está ya iniciada (después de configurar errores)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si es una petición API, manejar como JSON
    if (strpos($path, '/api/') === 0 || in_array($path, ['/auth/me', '/products', '/categories', '/suppliers', '/departments', '/locations', '/inventory/summary', '/reports/dashboard/stats', '/reports/inventory/summary', '/reports/inventory/low-stock', '/health', '/test', '/upload-photo', '/product-history'])) {
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
            exit;
    }
    
        // Determinar la ruta de la API
        if (strpos($path, '/api/') === 0) {
            $apiPath = substr($path, 5); // quitar '/api'
        } else {
            $apiPath = ltrim($path, '/'); // quitar la '/' inicial
        }
        
        error_log("API Path normalizado: $apiPath");
    
    // Enrutamiento de API
    switch ($apiPath) {
            case 'auth/me':
                try {
                    $db = DatabaseManager::getInstance();
                    $sessionId = session_id();
                    
                    // Verificar primero en $_SESSION
                    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
                        $user = $_SESSION['user'];
                        echo json_encode([
                            'success' => true,
                            'data' => $user,
                            'authenticated' => true
                        ]);
                    } else {
                        // Si no hay en $_SESSION, buscar en PostgreSQL
                        $user = $db->getSession($sessionId);
                        if ($user) {
                            // Guardar en $_SESSION para próxima vez
                            $_SESSION['user'] = $user;
                echo json_encode([
                                'success' => true,
                                'data' => $user,
                                'authenticated' => true
                ]);
            } else {
                            echo json_encode([
                                'success' => false,
                                'error' => 'No hay sesión activa',
                                'authenticated' => false,
                                'data' => null
                            ]);
                        }
                    }
                } catch (Exception $e) {
                echo json_encode([
                        'success' => false,
                        'error' => 'Error al recuperar sesión: ' . $e->getMessage(),
                    'authenticated' => false,
                        'data' => null
                ]);
            }
            break;
                
            case 'products':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    // Validar sesión activa - primero en $_SESSION
                    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
                        // Si no hay en $_SESSION, buscar en PostgreSQL
                        $sessionId = session_id();
                        $user = $db->getSession($sessionId);
                        if (!$user) {
            echo json_encode([
                                'success' => false,
                                'error' => 'No autorizado - Debes iniciar sesión'
            ]);
            break;
                        }
                        // Guardar en $_SESSION
                        $_SESSION['user'] = $user;
                    }
                    
                    // Obtener todos los productos desde DatabaseManager
                    $products = $db->getAllProducts();
                    
                    // Agregar información de categoría y proveedor
                    foreach ($products as &$product) {
                        $product['category_name'] = 'Electrónica'; // Por defecto
                        $product['supplier_name'] = 'Mouser Electronics'; // Por defecto
                    }
                    
                    safeLog('INFO', 'PRODUCT', 'LIST', 'Productos listados: ' . count($products));
                    error_log("Products GET: Devolviendo " . count($products) . " productos");
                    
            echo json_encode([
                        'success' => true,
                        'data' => $products
                    ]);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Crear nuevo producto
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        error_log("Products POST: Datos JSON inválidos");
                        echo json_encode(['success' => false, 'error' => 'Datos JSON inválidos']);
                        break;
                    }
                    
                    // Campos requeridos
                    $required = ['sku', 'name', 'serial_number', 'label'];
                    foreach ($required as $field) {
                        if (empty($input[$field])) {
                            error_log("Products POST: Campo requerido faltante: $field");
                            echo json_encode(['success' => false, 'error' => "El campo $field es requerido"]);
                            break 2;
                        }
                    }
                    
                    // Verificar campos únicos usando DatabaseManager
                    error_log("Products POST: Verificando SKU: " . $input['sku']);
                    if ($db->checkUniqueField('sku', $input['sku'])) {
                        error_log("Products POST: SKU duplicado encontrado: " . $input['sku']);
                        echo json_encode(['success' => false, 'error' => 'El SKU ya existe. Por favor usa un SKU diferente.']);
                        break;
                    }
                    error_log("Products POST: Verificando Serial: " . $input['serial_number']);
                    if ($db->checkUniqueField('serial_number', $input['serial_number'])) {
                        error_log("Products POST: Serial duplicado encontrado: " . $input['serial_number']);
                        echo json_encode(['success' => false, 'error' => 'El número de serial ya existe. Por favor usa un serial diferente.']);
                        break;
                    }
                    error_log("Products POST: Verificando Marbete: " . $input['label']);
                    if ($db->checkUniqueField('label', $input['label'])) {
                        error_log("Products POST: Marbete duplicado encontrado: " . $input['label']);
                        echo json_encode(['success' => false, 'error' => 'El marbete ya existe. Por favor usa un marbete diferente.']);
            break;
                    }
                    error_log("Products POST: Todos los campos únicos verificados correctamente");
                    
                    // Preparar datos para PostgreSQL
                    $productData = [
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
                        'type' => $input['type'] ?? 'computo',
                        'serial_number' => $input['serial_number'],
                        'department' => $input['department'] ?? '',
                        'location' => $input['location'] ?? '',
                        'label' => $input['label'] ?? '',
                        'barcode' => $input['barcode'] ?? '',
                        'expiration_date' => $input['expiration_date'] ?? null,
                        'status' => $input['status'] ?? 'active',
                        'photo_url' => $input['photo_url'] ?? null
                    ];
                    
                    // Crear producto usando DatabaseManager
                    $newProductId = $db->createProduct($productData);
                    
                    if (!$newProductId) {
                        error_log("Products POST: Error al crear producto - createProduct devolvió null o false");
                        echo json_encode(['success' => false, 'error' => 'Error al crear el producto en la base de datos']);
                        break;
                    }
                    
                    // Obtener el producto completo recién creado
                    $newProduct = $db->getProductById($newProductId);
                    
                    if (!$newProduct) {
                        error_log("Products POST: Error al recuperar producto creado con ID: " . $newProductId);
                        echo json_encode(['success' => false, 'error' => 'Producto creado pero no se pudo recuperar']);
                        break;
                    }
                    
                    safeLog('INFO', 'PRODUCT', 'CREATE', "Producto creado: {$newProduct['sku']} - {$newProduct['name']}");
                    error_log("Products POST: Producto creado exitosamente con ID: " . $newProductId);
                    
            echo json_encode([
                'success' => true,
                        'message' => 'Producto creado exitosamente',
                        'data' => $newProduct
                    ]);
                } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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
                    
                    // Verificar que el producto existe
                    $existingProduct = $db->getProductById($productId);
                    if (!$existingProduct) {
                        error_log("Products PUT: Producto no encontrado con ID: $productId");
                        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                        break;
                    }
                    
                    // Verificar campos únicos (excluyendo el producto actual)
                    if ($db->checkUniqueField('serial_number', $input['serial_number'], $productId)) {
                        error_log("Products PUT: Serial duplicado: " . $input['serial_number']);
                        echo json_encode(['success' => false, 'error' => 'El número de serial ya existe. Por favor usa un serial diferente.']);
                        break;
                    }
                    if ($db->checkUniqueField('label', $input['label'], $productId)) {
                        error_log("Products PUT: Marbete duplicado: " . $input['label']);
                        echo json_encode(['success' => false, 'error' => 'El marbete ya existe. Por favor usa un marbete diferente.']);
            break;
                    }
                    
                    // Preparar datos para actualización
                    error_log("Products PUT: Datos recibidos: " . json_encode($input));
                    $updateData = [
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
                        'type' => $input['type'] ?? 'computo',
                        'serial_number' => $input['serial_number'],
                        'department' => $input['department'] ?? '',
                        'label' => $input['label'] ?? '',
                        'barcode' => $input['barcode'] ?? '',
                        'expiration_date' => $input['expiration_date'] ?? null,
                        'status' => $input['status'] ?? 'active',
                        'photo_url' => $input['photo_url'] ?? null
                    ];
                    
                    // Actualizar producto usando DatabaseManager
                    error_log("Products PUT: Llamando a updateProduct con ID=$productId y datos: " . json_encode($updateData));
                    $success = $db->updateProduct($productId, $updateData);
                    error_log("Products PUT: Resultado de updateProduct: " . ($success ? 'true' : 'false'));
                    
                    if ($success) {
                        // Obtener el producto actualizado
                        $updatedProduct = $db->getProductById($productId);
                        error_log("Products PUT: Producto actualizado recuperado: " . json_encode($updatedProduct));
                        
                        if (!$updatedProduct) {
                            error_log("Products PUT: ERROR - No se pudo recuperar el producto actualizado");
                            echo json_encode(['success' => false, 'error' => 'Producto actualizado pero no se pudo recuperar']);
            break;
                        }
                        
                        safeLog('INFO', 'PRODUCT', 'UPDATE', "Producto actualizado: {$updatedProduct['sku']} - {$updatedProduct['name']}");
                        error_log("Products PUT: Producto actualizado exitosamente con ID: $productId");
            
            echo json_encode([
                'success' => true,
                            'message' => 'Producto actualizado exitosamente',
                            'data' => $updatedProduct
                        ]);
                    } else {
                        error_log("Products PUT: ERROR - updateProduct devolvió false para ID: $productId");
                        echo json_encode(['success' => false, 'error' => 'Error al actualizar el producto en la base de datos']);
                    }
                } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input || !isset($input['id'])) {
                        error_log("Products DELETE: ID faltante");
                        echo json_encode(['success' => false, 'error' => 'ID de producto requerido']);
                        break;
                    }
                    
                    $productId = intval($input['id']);
                    if (!$productId) {
                        error_log("Products DELETE: ID de producto inválido");
                        echo json_encode(['success' => false, 'error' => 'ID de producto inválido']);
                        break;
                    }
                    
                    // Verificar que el producto existe antes de eliminar
                    $existingProduct = $db->getProductById($productId);
                    if (!$existingProduct) {
                        error_log("Products DELETE: Producto no encontrado con ID: $productId");
                        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
            break;
                    }
                    
                    // Eliminar producto usando DatabaseManager
                    $success = $db->deleteProduct($productId);
                    
                    if ($success) {
                        safeLog('INFO', 'PRODUCT', 'DELETE', "Producto eliminado: {$existingProduct['sku']} - {$existingProduct['name']}");
                        error_log("Products DELETE: Producto eliminado exitosamente con ID: $productId");
                        
            echo json_encode([
                'success' => true,
                            'message' => 'Producto eliminado exitosamente',
                            'data' => $existingProduct
            ]);
                    } else {
                        error_log("Products DELETE: Error eliminando producto con ID: $productId");
                        echo json_encode(['success' => false, 'error' => 'Error al eliminar el producto']);
                    }
                }
            break;
            
            case 'product-history':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    // Verificar sesión - primero en $_SESSION, luego en base de datos
                    $userRole = '';
                    $user = null;
                    
                    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
                        $user = $_SESSION['user'];
                        $userRole = $user['role'] ?? '';
                        error_log("Product History: Rol obtenido de \$_SESSION: " . $userRole);
                    } else {
                        // Si no hay en $_SESSION, buscar en PostgreSQL
                        try {
                            $db = DatabaseManager::getInstance();
                            $sessionId = session_id();
                            $user = $db->getSession($sessionId);
                            if ($user) {
                                $userRole = $user['role'] ?? '';
                                // Guardar en $_SESSION para próxima vez
                                $_SESSION['user'] = $user;
                                error_log("Product History: Rol obtenido de DB: " . $userRole);
                            } else {
                                error_log("Product History: No se encontró sesión activa");
                            }
                        } catch (Exception $e) {
                            error_log("Product History: Error obteniendo sesión: " . $e->getMessage());
                        }
                    }
                    
                    // Verificar que el usuario es admin, manager o superior
                    if (empty($userRole) || !in_array($userRole, ['admin', 'superadmin', 'manager'])) {
                        error_log("Product History: Acceso denegado. Rol del usuario: '" . $userRole . "'. Roles permitidos: admin, superadmin, manager");
                        echo json_encode([
                            'success' => false,
                            'error' => 'Acceso denegado. Solo administradores y gerentes pueden ver el historial.',
                            'debug' => [
                                'user_role' => $userRole,
                                'session_user' => isset($_SESSION['user']) ? $_SESSION['user'] : null
                            ]
                        ]);
                        exit;
                    }
                    
                    // Obtener ID del producto desde query string
                    $productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
                    if (!$productId) {
                        echo json_encode([
                            'success' => false,
                            'error' => 'ID de producto requerido'
                        ]);
                        exit;
                    }
                    
                    try {
                        $db = DatabaseManager::getInstance();
                        $history = $db->getProductHistory($productId);
                        
                        if ($history === false || $history === null) {
                            $history = [];
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $history
                        ]);
                        exit;
                    } catch (Exception $e) {
                        error_log("Error obteniendo historial: " . $e->getMessage());
                        echo json_encode([
                            'success' => false,
                            'error' => 'Error al obtener el historial: ' . $e->getMessage()
                        ]);
                        exit;
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Método no permitido'
                    ]);
                    exit;
                }
            // break; removido porque todos los casos terminan con exit
            
            case 'reports/dashboard/stats':
                try {
                    // Obtener productos desde DatabaseManager
                    $products = $db->getAllProducts();
                    
                    // Proteger contra null
                    if (!is_array($products)) {
                        $products = [];
                    }
                    
                    $totalProducts = count($products);
                    $totalValue = 0;
                    $lowStockProducts = 0;
                    
                    foreach ($products as $product) {
                        $totalValue += $product['price'] * $product['stock_quantity'];
                        if ($product['stock_quantity'] <= $product['min_stock_level']) {
                            $lowStockProducts++;
                        }
                    }
                    
                    $stats = [
                        'totalProducts' => $totalProducts,
                        'totalValue' => $totalValue,
                        'lowStockProducts' => $lowStockProducts,
                        'totalSuppliers' => 4 // Valor fijo por ahora
                    ];
                    
                    safeLog('INFO', 'REPORT', 'DASHBOARD', 'Estadísticas del dashboard generadas');
                    error_log("Dashboard stats: Total productos: $totalProducts, Valor total: $totalValue, Stock bajo: $lowStockProducts");
                    
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
                } catch (Exception $e) {
                    error_log("Dashboard stats error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al generar estadísticas'
                    ]);
                }
                break;
                
            case 'departments':
                try {
                    $departments = [
                        ['id' => 1, 'name' => 'Telemática'],
                        ['id' => 2, 'name' => 'S1'],
                        ['id' => 3, 'name' => 'Protección'],
                        ['id' => 4, 'name' => 'S3']
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $departments
                    ]);
                } catch (Exception $e) {
                    error_log("Departments error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al cargar departamentos'
                    ]);
                }
                break;
                
            case 'locations':
                try {
                    $locations = [
                        ['id' => 1, 'name' => 'Almacén Principal'],
                        ['id' => 2, 'name' => 'Almacén Secundario'],
                        ['id' => 3, 'name' => 'Laboratorio'],
                        ['id' => 4, 'name' => 'Oficina']
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $locations
                    ]);
                } catch (Exception $e) {
                    error_log("Locations error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al cargar ubicaciones'
                    ]);
                }
                break;
                
            case 'categories':
                try {
                    $categories = [
                        ['id' => 1, 'name' => 'Electrónica', 'description' => 'Componentes electrónicos', 'product_count' => 0],
                        ['id' => 2, 'name' => 'Iluminación', 'description' => 'Sistemas de iluminación', 'product_count' => 0],
                        ['id' => 3, 'name' => 'Resistores', 'description' => 'Resistencias eléctricas', 'product_count' => 0],
                        ['id' => 4, 'name' => 'Capacitores', 'description' => 'Capacitores eléctricos', 'product_count' => 0],
                        ['id' => 5, 'name' => 'Cables', 'description' => 'Cables y conectores', 'product_count' => 0]
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $categories
                    ]);
                } catch (Exception $e) {
                    error_log("Categories error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al cargar categorías'
                    ]);
                }
                break;
                
            case 'suppliers':
                try {
                    $suppliers = [
                        ['id' => 1, 'name' => 'Mouser Electronics', 'contact_person' => 'John Smith', 'email' => 'contact@mouser.com', 'phone' => '+1-555-0123', 'product_count' => 0],
                        ['id' => 2, 'name' => 'Digi-Key Electronics', 'contact_person' => 'Jane Doe', 'email' => 'contact@digikey.com', 'phone' => '+1-555-0124', 'product_count' => 0],
                        ['id' => 3, 'name' => 'Farnell', 'contact_person' => 'Bob Johnson', 'email' => 'contact@farnell.com', 'phone' => '+1-555-0125', 'product_count' => 0],
                        ['id' => 4, 'name' => 'RS Components', 'contact_person' => 'Alice Brown', 'email' => 'contact@rs-components.com', 'phone' => '+1-555-0126', 'product_count' => 0]
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $suppliers
                    ]);
                } catch (Exception $e) {
                    error_log("Suppliers error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al cargar proveedores'
                    ]);
                }
                break;
                
            case 'inventory/summary':
                try {
                    // Validar sesión activa - primero en $_SESSION
                    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
                        // Si no hay en $_SESSION, buscar en PostgreSQL
                        $sessionId = session_id();
                        $user = $db->getSession($sessionId);
                        if (!$user) {
                            echo json_encode([
                                'success' => false,
                                'error' => 'No autorizado - Debes iniciar sesión'
                            ]);
                            break;
                        }
                        // Guardar en $_SESSION
                        $_SESSION['user'] = $user;
                    }
                    
                    // Obtener productos usando DatabaseManager
                    $products = $db->getAllProducts();
                    
                    // Proteger contra null
                    if (!is_array($products)) {
                        $products = [];
                    }
                    
                    $totalProducts = count($products);
                    $totalValue = 0;
                    $lowStockProducts = [];
                    $departments = [];
                    $uniqueDepartments = [];
                    
                    foreach ($products as $product) {
                        $totalValue += $product['price'] * $product['stock_quantity'];
                        // Producto con stock bajo si: stock = 0 o stock <= min_stock_level
                        if ($product['stock_quantity'] == 0 || ($product['min_stock_level'] > 0 && $product['stock_quantity'] <= $product['min_stock_level'])) {
                            $lowStockProducts[] = $product;
                        }
                        
                        // Contar departamentos únicos
                        $dept = $product['department'] ?: 'Sin asignar';
                        if (!in_array($dept, $uniqueDepartments)) {
                            $uniqueDepartments[] = $dept;
                        }
                        
                        if (!isset($departments[$dept])) {
                            $departments[$dept] = 0;
                        }
                        $departments[$dept]++;
                    }
                    
                    $summary = [
                        'total_products' => $totalProducts,
                        'total_value' => $totalValue,
                        'low_stock_products' => $lowStockProducts,
                        'departments' => $departments,
                        'unique_departments_count' => count($uniqueDepartments),
                        'unique_departments' => $uniqueDepartments
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $summary
                    ]);
                } catch (Exception $e) {
                    error_log("Inventory summary error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al generar resumen de inventario'
                    ]);
                }
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
            
            case 'postgresql':
            // Test de conexión PostgreSQL
            $status = $db->getConnectionStatus();
            echo json_encode([
                'success' => true,
                'message' => 'Test de PostgreSQL',
                'connection_status' => $status,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'upload-photo':
            // Endpoint para subir fotos de productos
            // Asegurar que se devuelva JSON
            header('Content-Type: application/json');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Método no permitido']);
                exit;
            }
            
            // Verificar autenticación
            $authenticated = false;
            if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
                $authenticated = true;
            } else {
                $sessionId = session_id();
                $user = $db->getSession($sessionId);
                if ($user) {
                    $_SESSION['user'] = $user;
                    $authenticated = true;
                }
            }
            
            if (!$authenticated) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'No autenticado']);
                exit;
            }
            
            // Verificar que se haya enviado una imagen
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen o hubo un error en la subida']);
                exit;
            }
            
            $file = $_FILES['photo'];
            
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido. Solo se permiten imágenes (JPEG, PNG, GIF, WEBP)']);
                exit;
            }
            
            // Validar tamaño (máximo 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El archivo es demasiado grande. Máximo 5MB']);
                exit;
            }
            
            // Intentar usar sistema de archivos primero, pero si falla, usar base64 automáticamente
            $photosDir = __DIR__ . '/uploads/photos';
            $uploadsDir = __DIR__ . '/uploads';
            $useFileSystem = false;
            
            // Verificar si podemos usar sistema de archivos
            if (is_dir($uploadsDir) && is_writable($uploadsDir)) {
                // El directorio uploads existe y es escribible
                if (is_dir($photosDir) && is_writable($photosDir)) {
                    $useFileSystem = true;
                } elseif (!is_dir($photosDir)) {
                    // Intentar crear el directorio photos
                    if (@mkdir($photosDir, 0755, true) && is_writable($photosDir)) {
                        $useFileSystem = true;
                    }
                }
            } elseif (!is_dir($uploadsDir)) {
                // Intentar crear el directorio uploads
                if (@mkdir($uploadsDir, 0755, true) && is_writable($uploadsDir)) {
                    // Ahora intentar crear photos
                    if (@mkdir($photosDir, 0755, true) && is_writable($photosDir)) {
                        $useFileSystem = true;
                    }
                }
            }
            
            // Si no podemos usar sistema de archivos, usar base64 directamente
            if (!$useFileSystem) {
                error_log("Info: Usando almacenamiento base64 (sistema de archivos no disponible o sin permisos)");
                // Continuar directamente a guardar como base64
            }
            
            // Generar nombre único para el archivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (empty($extension)) {
                // Si no hay extensión, intentar detectarla del tipo MIME
                $extension = 'jpg'; // Por defecto
                if (strpos($mimeType, 'png') !== false) $extension = 'png';
                elseif (strpos($mimeType, 'gif') !== false) $extension = 'gif';
                elseif (strpos($mimeType, 'webp') !== false) $extension = 'webp';
            }
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $photosDir . '/' . $filename;
            
            // Verificar que el archivo temporal existe
            if (!file_exists($file['tmp_name'])) {
                error_log("Error: Archivo temporal no existe: " . $file['tmp_name']);
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'error' => 'El archivo temporal no se encontró. Intente nuevamente.'
                ]);
                exit;
            }
            
            // Intentar guardar en sistema de archivos primero
            if ($useFileSystem) {
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Verificar que el archivo se guardó correctamente
                    if (!file_exists($filepath)) {
                        error_log("Error: El archivo no se guardó correctamente: $filepath");
                        // Intentar guardar como base64 como respaldo
                        $useFileSystem = false;
                    } else {
                        // Generar URL relativa
                        $photoUrl = '/uploads/photos/' . $filename;
                        
                        safeLog('INFO', 'PRODUCT', 'PHOTO_UPLOAD', "Foto subida: $filename");
                        echo json_encode([
                            'success' => true,
                            'photo_url' => $photoUrl,
                            'message' => 'Foto subida exitosamente',
                            'storage' => 'filesystem'
                        ]);
                        exit;
                    }
                } else {
                    // Si falla, intentar guardar como base64
                    error_log("Warning: No se pudo guardar en sistema de archivos, usando base64");
                    $useFileSystem = false;
                }
            }
            
            // Si no se puede usar sistema de archivos, guardar como base64 en la base de datos
            if (!$useFileSystem) {
                // Leer el contenido del archivo
                $imageData = file_get_contents($file['tmp_name']);
                if ($imageData === false) {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false, 
                        'error' => 'No se pudo leer el contenido de la imagen.'
                    ]);
                    exit;
                }
                
                // Convertir a base64
                $base64Image = base64_encode($imageData);
                $dataUri = 'data:' . $mimeType . ';base64,' . $base64Image;
                
                // Guardar en la base de datos (usaremos un formato especial para identificar que es base64)
                // En lugar de guardar la URL, guardaremos un identificador que indique que está en base64
                // Por ahora, guardaremos la data URI completa (aunque es larga, funciona)
                $photoUrl = $dataUri;
                
                safeLog('INFO', 'PRODUCT', 'PHOTO_UPLOAD', "Foto subida como base64 (sistema de archivos no disponible)");
                echo json_encode([
                    'success' => true,
                    'photo_url' => $photoUrl,
                    'message' => 'Foto subida exitosamente (almacenada en base de datos)',
                    'storage' => 'database'
                ]);
                exit;
            }
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                    'error' => 'Endpoint no encontrado',
                    'path' => $apiPath
            ]);
            break;
    }
    exit; // Salir después de manejar la API
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