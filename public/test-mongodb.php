<?php
// Script para verificar conexión a MongoDB Atlas
require_once __DIR__ . '/vendor/autoload.php';

echo "=== VERIFICACIÓN DE CONEXIÓN MONGODB ===\n";

// Verificar si la extensión MongoDB está instalada
if (!extension_loaded('mongodb')) {
    echo "❌ ERROR: Extensión MongoDB no está instalada\n";
    echo "Solución: Instalar extensión PHP MongoDB\n";
    exit(1);
} else {
    echo "✅ Extensión MongoDB instalada\n";
}

// Verificar si la clase MongoDB\Client existe
if (!class_exists('MongoDB\Client')) {
    echo "❌ ERROR: Clase MongoDB\\Client no encontrada\n";
    echo "Solución: Ejecutar 'composer install'\n";
    exit(1);
} else {
    echo "✅ Clase MongoDB\\Client disponible\n";
}

// Verificar variable de entorno
$uri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');
if (!$uri) {
    echo "❌ ERROR: Variable MONGODB_URI no configurada\n";
    echo "Solución: Configurar MONGODB_URI en Render\n";
    exit(1);
} else {
    echo "✅ Variable MONGODB_URI configurada\n";
    echo "URI: " . substr($uri, 0, 20) . "...\n";
}

// Intentar conectar
try {
    $client = new MongoDB\Client($uri);
    $database = $client->selectDatabase('inventory_db');
    $collection = $database->selectCollection('products');
    
    // Probar conexión
    $result = $collection->countDocuments();
    echo "✅ Conexión exitosa a MongoDB Atlas\n";
    echo "Productos en la base de datos: $result\n";
    
    // Probar inserción
    $testProduct = [
        'sku' => 'TEST-' . time(),
        'name' => 'Producto de prueba',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'test' => true
    ];
    
    $insertResult = $collection->insertOne($testProduct);
    echo "✅ Inserción de prueba exitosa\n";
    
    // Limpiar producto de prueba
    $collection->deleteOne(['_id' => $insertResult->getInsertedId()]);
    echo "✅ Limpieza de prueba exitosa\n";
    
    echo "\n🎉 MONGODB ATLAS FUNCIONANDO CORRECTAMENTE\n";
    
} catch (Exception $e) {
    echo "❌ ERROR de conexión: " . $e->getMessage() . "\n";
    echo "Solución: Verificar string de conexión y permisos\n";
    exit(1);
}
?>
