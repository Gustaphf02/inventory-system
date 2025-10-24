<?php
// Archivo simple para probar MongoDB
header('Content-Type: application/json');

echo "=== VERIFICACIÓN MONGODB ===\n";

// Verificar si MongoDB está configurado
$mongoUri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');

if (!$mongoUri) {
    echo json_encode([
        'status' => 'error',
        'message' => 'MONGODB_URI no configurado',
        'mongodb_available' => false
    ]);
    exit;
}

echo "✅ MONGODB_URI configurado\n";

// Verificar si la extensión MongoDB está instalada
if (!extension_loaded('mongodb')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Extensión MongoDB no instalada',
        'mongodb_available' => false
    ]);
    exit;
}

echo "✅ Extensión MongoDB instalada\n";

// Verificar si la clase MongoDB\Client existe
if (!class_exists('MongoDB\Client')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Clase MongoDB\\Client no encontrada',
        'mongodb_available' => false
    ]);
    exit;
}

echo "✅ Clase MongoDB\\Client disponible\n";

// Intentar conectar
try {
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
?>
