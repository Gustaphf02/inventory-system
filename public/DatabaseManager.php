<?php
// Configuración de MongoDB Atlas con fallback a JSON
class DatabaseManager {
    private static $instance = null;
    private $mongo = null;
    private $useMongoDB = false;
    
    private function __construct() {
        try {
            // Intentar conectar a MongoDB
            $uri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI');
            
            if ($uri && class_exists('MongoDB\Client')) {
                require_once __DIR__ . '/MongoDBConnection.php';
                $this->mongo = MongoDBConnection::getInstance();
                $this->useMongoDB = true;
                error_log("MongoDB Atlas conectado exitosamente");
            } else {
                error_log("MongoDB no disponible, usando archivos JSON");
                $this->useMongoDB = false;
            }
        } catch (Exception $e) {
            error_log("Error conectando a MongoDB: " . $e->getMessage());
            error_log("Usando archivos JSON como fallback");
            $this->useMongoDB = false;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getAllProducts() {
        if ($this->useMongoDB) {
            return $this->mongo->getAllProducts();
        } else {
            return $this->loadProductsFromFile();
        }
    }
    
    public function createProduct($data) {
        if ($this->useMongoDB) {
            $id = $this->mongo->createProduct($data);
            return $this->mongo->getProductById($id);
        } else {
            return $this->createProductInFile($data);
        }
    }
    
    public function updateProduct($id, $data) {
        if ($this->useMongoDB) {
            return $this->mongo->updateProduct($id, $data);
        } else {
            return $this->updateProductInFile($id, $data);
        }
    }
    
    public function deleteProduct($id) {
        if ($this->useMongoDB) {
            return $this->mongo->deleteProduct($id);
        } else {
            return $this->deleteProductInFile($id);
        }
    }
    
    public function checkUniqueField($field, $value, $excludeId = null) {
        if ($this->useMongoDB) {
            return $this->mongo->checkUniqueField($field, $value, $excludeId);
        } else {
            return $this->checkUniqueFieldInFile($field, $value, $excludeId);
        }
    }
    
    // Métodos para archivos JSON (fallback)
    private function loadProductsFromFile() {
        $file = __DIR__ . '/data/products.json';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);
            return is_array($data) ? $data : [];
        }
        return [];
    }
    
    private function saveProductsToFile($products) {
        $file = __DIR__ . '/data/products.json';
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT));
    }
    
    private function createProductInFile($data) {
        $products = $this->loadProductsFromFile();
        $data['id'] = count($products) + 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $products[] = $data;
        $this->saveProductsToFile($products);
        
        return $data;
    }
    
    private function updateProductInFile($id, $data) {
        $products = $this->loadProductsFromFile();
        foreach ($products as $index => $product) {
            if ($product['id'] == $id) {
                $data['id'] = $id;
                $data['created_at'] = $product['created_at'];
                $data['updated_at'] = date('Y-m-d H:i:s');
                $products[$index] = $data;
                $this->saveProductsToFile($products);
                return true;
            }
        }
        return false;
    }
    
    private function deleteProductInFile($id) {
        $products = $this->loadProductsFromFile();
        foreach ($products as $index => $product) {
            if ($product['id'] == $id) {
                unset($products[$index]);
                $products = array_values($products);
                $this->saveProductsToFile($products);
                return true;
            }
        }
        return false;
    }
    
    private function checkUniqueFieldInFile($field, $value, $excludeId = null) {
        $products = $this->loadProductsFromFile();
        foreach ($products as $product) {
            if ($excludeId && $product['id'] == $excludeId) continue;
            if (strtolower($product[$field] ?? '') === strtolower($value)) {
                return true;
            }
        }
        return false;
    }
}
?>
