<?php
/**
 * Sistema de Inventario - Versión sin Base de Datos
 * Funciona con datos de ejemplo en memoria
 */

// Datos de ejemplo en memoria
$sampleData = [
    'products' => [
        [
            'id' => 1,
            'sku' => 'RES-1K-1/4W',
            'name' => 'Resistor 1K Ohm 1/4W',
            'description' => 'Resistor de carbón 1K Ohm, 1/4 Watt, tolerancia 5%',
            'price' => 0.10,
            'cost' => 0.05,
            'stock_quantity' => 1000,
            'min_stock_level' => 100,
            'category_id' => 3,
            'supplier_id' => 1,
            'category_name' => 'Resistores',
            'supplier_name' => 'Mouser Electronics',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 2,
            'sku' => 'CAP-100NF-50V',
            'name' => 'Capacitor Cerámico 100nF',
            'description' => 'Capacitor cerámico 100nF, 50V, X7R',
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
    ]
];

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

// Obtener la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remover el prefijo /api si existe
if (strpos($path, '/api/') === 0) {
    $path = substr($path, 4);
}

// Enrutamiento simple
switch ($path) {
    case '/health':
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'mode' => 'demo'
        ]);
        break;
        
    case '/products':
        echo json_encode([
            'success' => true,
            'data' => $sampleData['products'],
            'total' => count($sampleData['products'])
        ]);
        break;
        
    case '/categories':
        echo json_encode([
            'success' => true,
            'data' => $sampleData['categories']
        ]);
        break;
        
    case '/suppliers':
        echo json_encode([
            'success' => true,
            'data' => $sampleData['suppliers']
        ]);
        break;
        
    case '/reports/dashboard/stats':
        $stats = calculateStats($sampleData['products']);
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;
        
    case '/reports/inventory/summary':
        $stats = calculateStats($sampleData['products']);
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        break;
        
    case '/reports/inventory/low-stock':
        $lowStockProducts = array_filter($sampleData['products'], function($p) { 
            return $p['stock_quantity'] <= $p['min_stock_level']; 
        });
        echo json_encode([
            'success' => true,
            'data' => array_values($lowStockProducts)
        ]);
        break;
        
    default:
        // Si no es una ruta de API, mostrar la página principal
        if (strpos($path, '/api/') !== 0) {
            // Incluir la página HTML principal
            include 'index.html';
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint no encontrado: ' . $path
            ]);
        }
        break;
}
?>
