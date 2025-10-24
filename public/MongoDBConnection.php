<?php
// Configuración de MongoDB Atlas
class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    private $collection;
    
    private function __construct() {
        try {
            // Obtener URI de MongoDB desde variable de entorno
            $uri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');
            
            if (!$uri) {
                // Fallback para desarrollo local
                $uri = 'mongodb://localhost:27017';
            }
            
            // Crear cliente MongoDB
            $this->client = new MongoDB\Client($uri);
            
            // Seleccionar base de datos y colección
            $this->database = $this->client->selectDatabase('inventory_db');
            $this->collection = $this->database->selectCollection('products');
            
            // Crear índices únicos
            $this->createIndexes();
            
        } catch (Exception $e) {
            error_log("MongoDB connection error: " . $e->getMessage());
            throw new Exception("Error de conexión a MongoDB");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getCollection() {
        return $this->collection;
    }
    
    private function createIndexes() {
        try {
            // Crear índices únicos para campos que no pueden duplicarse
            $this->collection->createIndex(['sku' => 1], ['unique' => true]);
            $this->collection->createIndex(['serial_number' => 1], ['unique' => true]);
            $this->collection->createIndex(['label' => 1], ['unique' => true]);
            
            // Índice para búsquedas rápidas
            $this->collection->createIndex(['name' => 1]);
            $this->collection->createIndex(['department' => 1]);
            $this->collection->createIndex(['status' => 1]);
            
        } catch (Exception $e) {
            error_log("MongoDB index creation error: " . $e->getMessage());
        }
    }
    
    public function getAllProducts() {
        try {
            $cursor = $this->collection->find([], ['sort' => ['created_at' => -1]]);
            $products = [];
            
            foreach ($cursor as $document) {
                $product = $document->toArray();
                $product['id'] = (string)$product['_id'];
                unset($product['_id']);
                $products[] = $product;
            }
            
            return $products;
        } catch (Exception $e) {
            error_log("MongoDB getAllProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getProductById($id) {
        try {
            $document = $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            
            if ($document) {
                $product = $document->toArray();
                $product['id'] = (string)$product['_id'];
                unset($product['_id']);
                return $product;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("MongoDB getProductById error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createProduct($data) {
        try {
            // Agregar timestamps
            $data['created_at'] = new MongoDB\BSON\UTCDateTime();
            $data['updated_at'] = new MongoDB\BSON\UTCDateTime();
            
            $result = $this->collection->insertOne($data);
            return (string)$result->getInsertedId();
        } catch (Exception $e) {
            error_log("MongoDB createProduct error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateProduct($id, $data) {
        try {
            // Agregar timestamp de actualización
            $data['updated_at'] = new MongoDB\BSON\UTCDateTime();
            
            $result = $this->collection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => $data]
            );
            
            return $result->getModifiedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB updateProduct error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function deleteProduct($id) {
        try {
            $result = $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            return $result->getDeletedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB deleteProduct error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function checkUniqueField($field, $value, $excludeId = null) {
        try {
            $filter = [$field => $value];
            
            if ($excludeId) {
                $filter['_id'] = ['$ne' => new MongoDB\BSON\ObjectId($excludeId)];
            }
            
            $count = $this->collection->countDocuments($filter);
            return $count > 0;
        } catch (Exception $e) {
            error_log("MongoDB checkUniqueField error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProductsCount() {
        try {
            return $this->collection->countDocuments();
        } catch (Exception $e) {
            error_log("MongoDB getProductsCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getLowStockProducts() {
        try {
            $cursor = $this->collection->find([
                '$expr' => [
                    '$lte' => ['$stock_quantity', '$min_stock_level']
                ]
            ]);
            
            $products = [];
            foreach ($cursor as $document) {
                $product = $document->toArray();
                $product['id'] = (string)$product['_id'];
                unset($product['_id']);
                $products[] = $product;
            }
            
            return $products;
        } catch (Exception $e) {
            error_log("MongoDB getLowStockProducts error: " . $e->getMessage());
            return [];
        }
    }
}
?>
