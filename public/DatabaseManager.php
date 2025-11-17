<?php
/**
 * DatabaseManager para PostgreSQL
 * Maneja la conexión y operaciones con PostgreSQL usando PDO
 */

class DatabaseManager {
    private static $instance = null;
    private $pdo;
    private $usePostgreSQL = false;
    private $fallbackToJSON = true;

    private function __construct() {
        try {
            // Intentar conectar a PostgreSQL
            $this->connectToPostgreSQL();
            $this->usePostgreSQL = true;
            $this->createTablesIfNotExist();
            error_log("DatabaseManager: Conectado a PostgreSQL exitosamente");
        } catch (Exception $e) {
            error_log("DatabaseManager: Error conectando a PostgreSQL: " . $e->getMessage());
            $this->usePostgreSQL = false;
            if ($this->fallbackToJSON) {
                error_log("DatabaseManager: Usando fallback JSON");
            }
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DatabaseManager();
        }
        return self::$instance;
    }

    private function connectToPostgreSQL() {
        // Obtener DATABASE_URL desde variables de entorno
        $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
        
        if (empty($databaseUrl)) {
            throw new Exception("DATABASE_URL no configurada");
        }

        // Parsear la URL de conexión
        $parsedUrl = parse_url($databaseUrl);
        
        $host = $parsedUrl['host'];
        $port = $parsedUrl['port'] ?? 5432;
        $dbname = ltrim($parsedUrl['path'], '/');
        $username = $parsedUrl['user'];
        $password = $parsedUrl['pass'];

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    private function createTablesIfNotExist() {
        // Crear tabla de sesiones solo si no existe
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id INTEGER NOT NULL,
                email VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL,
                username VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL
            )
        ");
        
        $sql = "
            CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                sku VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                brand VARCHAR(100),
                model VARCHAR(100),
                price DECIMAL(10,2) DEFAULT 0,
                cost DECIMAL(10,2) DEFAULT 0,
                stock_quantity INTEGER DEFAULT 0,
                min_stock_level INTEGER DEFAULT 0,
                max_stock_level INTEGER DEFAULT 0,
                category_id INTEGER DEFAULT 1,
                supplier_id INTEGER DEFAULT 1,
                type VARCHAR(50) DEFAULT 'computo',
                serial_number VARCHAR(100) UNIQUE,
                department VARCHAR(100),
                label VARCHAR(100) UNIQUE,
                barcode VARCHAR(100),
                expiration_date DATE,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- Agregar columna type si no existe (para tablas existentes)
            DO \$\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                              WHERE table_name = 'products' AND column_name = 'type') THEN
                    ALTER TABLE products ADD COLUMN type VARCHAR(50) DEFAULT 'computo';
                END IF;
            END
            \$\$;
            
            -- Eliminar columna location si existe (para tablas existentes)
            DO \$\$
            BEGIN
                IF EXISTS (SELECT 1 FROM information_schema.columns 
                          WHERE table_name = 'products' AND column_name = 'location') THEN
                    ALTER TABLE products DROP COLUMN location;
                END IF;
            END
            \$\$;
            
            -- Agregar columna photo_url si no existe (para tablas existentes)
            DO \$\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                              WHERE table_name = 'products' AND column_name = 'photo_url') THEN
                    ALTER TABLE products ADD COLUMN photo_url TEXT;
                ELSIF EXISTS (SELECT 1 FROM information_schema.columns 
                             WHERE table_name = 'products' AND column_name = 'photo_url' 
                             AND data_type = 'character varying' AND character_maximum_length < 10000) THEN
                    -- Si existe pero es VARCHAR pequeño, cambiarlo a TEXT para soportar base64
                    ALTER TABLE products ALTER COLUMN photo_url TYPE TEXT;
                END IF;
            END
            \$\$;

            CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS suppliers (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                contact_person VARCHAR(100),
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            -- Insertar datos iniciales si no existen
            INSERT INTO categories (id, name, description) VALUES 
            (1, 'Electrónica', 'Componentes electrónicos'),
            (2, 'Computación', 'Equipos de computación'),
            (3, 'Redes', 'Equipos de red'),
            (4, 'Accesorios', 'Accesorios varios')
            ON CONFLICT (id) DO NOTHING;

            INSERT INTO suppliers (id, name, contact_person, email, phone) VALUES 
            (1, 'Proveedor Principal', 'Juan Pérez', 'contacto@proveedor.com', '+1234567890'),
            (2, 'Distribuidor Secundario', 'María García', 'ventas@distribuidor.com', '+0987654321')
            ON CONFLICT (id) DO NOTHING;
        ";

        $this->pdo->exec($sql);
    }

    public function getAllProducts() {
        try {
            if ($this->usePostgreSQL) {
                $stmt = $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC");
                $products = $stmt->fetchAll();
                error_log("DatabaseManager getAllProducts: Devolviendo " . count($products) . " productos");
                if (!empty($products)) {
                    error_log("DatabaseManager getAllProducts: Primer producto sample: " . json_encode($products[0]));
                }
                return $products;
            } else {
                return $this->loadProductsFromFile();
            }
        } catch (Exception $e) {
            error_log("DatabaseManager getAllProducts error: " . $e->getMessage());
            return $this->fallbackToJSON ? $this->loadProductsFromFile() : [];
        }
    }

    public function getProductById($id) {
        try {
            if ($this->usePostgreSQL) {
                $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                return $stmt->fetch();
            } else {
                return $this->getProductByIdInFile($id);
            }
        } catch (Exception $e) {
            error_log("DatabaseManager getProductById error: " . $e->getMessage());
            return $this->fallbackToJSON ? $this->getProductByIdInFile($id) : null;
        }
    }

    public function createProduct($data) {
        try {
            if ($this->usePostgreSQL) {
                $sql = "INSERT INTO products (sku, name, description, brand, model, price, cost, stock_quantity, min_stock_level, max_stock_level, category_id, supplier_id, type, serial_number, department, label, barcode, expiration_date, status, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                error_log("DatabaseManager createProduct: Ejecutando INSERT con datos: " . json_encode($data));
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['sku'],
                    $data['name'],
                    $data['description'] ?? '',
                    $data['brand'] ?? '',
                    $data['model'] ?? '',
                    $data['price'] ?? 0,
                    $data['cost'] ?? 0,
                    $data['stock_quantity'] ?? 0,
                    $data['min_stock_level'] ?? 0,
                    $data['max_stock_level'] ?? 0,
                    $data['category_id'] ?? 1,
                    $data['supplier_id'] ?? 1,
                    $data['type'] ?? 'computo',
                    $data['serial_number'] ?? '',
                    $data['department'] ?? '',
                    $data['label'] ?? '',
                    $data['barcode'] ?? '',
                    $data['expiration_date'] ?? null,
                    $data['status'] ?? 'active',
                    $data['photo_url'] ?? null
                ]);

                $newId = $this->pdo->lastInsertId();
                error_log("DatabaseManager createProduct: Producto creado con ID: " . $newId);
                return $newId;
            } else {
                return $this->createProductInFile($data);
            }
        } catch (Exception $e) {
            error_log("DatabaseManager createProduct error: " . $e->getMessage());
            return $this->fallbackToJSON ? $this->createProductInFile($data) : null;
        }
    }

    public function updateProduct($id, $data) {
        error_log("DatabaseManager updateProduct: Actualizando producto ID $id con datos: " . json_encode($data));
        try {
            if ($this->usePostgreSQL) {
                $sql = "UPDATE products SET name = ?, description = ?, brand = ?, model = ?, price = ?, cost = ?, stock_quantity = ?, min_stock_level = ?, max_stock_level = ?, category_id = ?, supplier_id = ?, type = ?, serial_number = ?, department = ?, label = ?, barcode = ?, expiration_date = ?, status = ?, photo_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                
                error_log("DatabaseManager updateProduct: SQL: " . $sql);
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([
                    $data['name'],
                    $data['description'] ?? '',
                    $data['brand'] ?? '',
                    $data['model'] ?? '',
                    $data['price'] ?? 0,
                    $data['cost'] ?? 0,
                    $data['stock_quantity'] ?? 0,
                    $data['min_stock_level'] ?? 0,
                    $data['max_stock_level'] ?? 0,
                    $data['category_id'] ?? 1,
                    $data['supplier_id'] ?? 1,
                    $data['type'] ?? 'computo',
                    $data['serial_number'] ?? '',
                    $data['department'] ?? '',
                    $data['label'] ?? '',
                    $data['barcode'] ?? '',
                    $data['expiration_date'] ?? null,
                    $data['status'] ?? 'active',
                    $data['photo_url'] ?? null,
                    $id
                ]);

                $rowCount = $stmt->rowCount();
                error_log("DatabaseManager updateProduct: Filas afectadas: $rowCount");
                return $rowCount > 0;
            } else {
                return $this->updateProductInFile($id, $data);
            }
        } catch (Exception $e) {
            error_log("DatabaseManager updateProduct error: " . $e->getMessage());
            return $this->fallbackToJSON ? $this->updateProductInFile($id, $data) : false;
        }
    }

    public function deleteProduct($id) {
        try {
            if ($this->usePostgreSQL) {
                $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                return $stmt->rowCount() > 0;
            } else {
                return $this->deleteProductInFile($id);
            }
        } catch (Exception $e) {
            error_log("DatabaseManager deleteProduct error: " . $e->getMessage());
            return $this->fallbackToJSON ? $this->deleteProductInFile($id) : false;
        }
    }

    public function checkUniqueField($field, $value, $excludeId = null) {
        error_log("DatabaseManager checkUniqueField: Verificando campo '$field' con valor '$value'");
        try {
            if ($this->usePostgreSQL) {
                $sql = "SELECT COUNT(*) FROM products WHERE $field = ?";
                $params = [$value];
                
                if ($excludeId) {
                    $sql .= " AND id != ?";
                    $params[] = $excludeId;
                }
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $count = $stmt->fetchColumn();
                error_log("DatabaseManager checkUniqueField: PostgreSQL count = $count");
                return $count > 0;
            } else {
                error_log("DatabaseManager checkUniqueField: Usando fallback JSON");
                return $this->checkUniqueFieldInFile($field, $value, $excludeId);
            }
        } catch (Exception $e) {
            error_log("DatabaseManager checkUniqueField error: " . $e->getMessage());
            // Siempre usar fallback JSON si PostgreSQL falla
            return $this->checkUniqueFieldInFile($field, $value, $excludeId);
        }
    }

    // Métodos de fallback JSON (mantener compatibilidad)
    private function loadProductsFromFile() {
        $file = __DIR__ . '/data/products.json';
        $dir = dirname($file);
        
        try {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            if (!file_exists($file)) {
                file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
                return [];
            }
            
            $content = file_get_contents($file);
            if ($content === false) {
                return [];
            }
            
            $data = json_decode($content, true);
            return is_array($data) ? $data : [];
            
        } catch (Exception $e) {
            error_log("DatabaseManager loadProductsFromFile error: " . $e->getMessage());
            return [];
        }
    }

    private function getProductByIdInFile($id) {
        $products = $this->loadProductsFromFile();
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
        return null;
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
        foreach ($products as &$product) {
            if ($product['id'] == $id) {
                $data['id'] = $id;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $product = array_merge($product, $data);
                $this->saveProductsToFile($products);
                return true;
            }
        }
        return false;
    }

    private function deleteProductInFile($id) {
        $products = $this->loadProductsFromFile();
        $products = array_filter($products, function($product) use ($id) {
            return $product['id'] != $id;
        });
        $this->saveProductsToFile(array_values($products));
        return true;
    }

    private function checkUniqueFieldInFile($field, $value, $excludeId = null) {
        $products = $this->loadProductsFromFile();
        error_log("DatabaseManager checkUniqueFieldInFile: Verificando '$field' = '$value' en " . count($products) . " productos");
        
        foreach ($products as $product) {
            if ($excludeId && $product['id'] == $excludeId) {
                continue;
            }
            if (isset($product[$field]) && $product[$field] === $value) {
                error_log("DatabaseManager checkUniqueFieldInFile: DUPLICADO encontrado en producto ID " . $product['id']);
                return true;
            }
        }
        error_log("DatabaseManager checkUniqueFieldInFile: No se encontraron duplicados");
        return false;
    }

    private function saveProductsToFile($products) {
        $file = __DIR__ . '/data/products.json';
        $dir = dirname($file);
        
        try {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log("DatabaseManager saveProductsToFile error: " . $e->getMessage());
        }
    }

    public function saveSession($sessionId, $userData) {
        try {
            // Limpiar sesiones expiradas
            $this->pdo->exec("DELETE FROM sessions WHERE expires_at < NOW()");
            
            // Guardar nueva sesión
            $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 horas
            $stmt = $this->pdo->prepare("
                INSERT INTO sessions (id, user_id, email, name, role, username, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (id) DO UPDATE SET 
                    email = EXCLUDED.email,
                    name = EXCLUDED.name,
                    role = EXCLUDED.role,
                    username = EXCLUDED.username,
                    expires_at = EXCLUDED.expires_at
            ");
            $stmt->execute([
                $sessionId,
                1, // user_id placeholder
                $userData['email'],
                $userData['name'],
                $userData['role'],
                $userData['username'],
                $expiresAt
            ]);
            
            error_log("Session saved: $sessionId for user: " . $userData['email']);
            return true;
        } catch (PDOException $e) {
            error_log("Error saving session: " . $e->getMessage());
            return false;
        }
    }

    public function getSession($sessionId) {
        try {
            if (empty($sessionId)) {
                error_log("getSession: sessionId is empty");
                
                // Si no hay sessionId, buscar la última sesión activa
                $stmt = $this->pdo->prepare("
                    SELECT * FROM sessions 
                    WHERE expires_at > NOW()
                    ORDER BY created_at DESC
                    LIMIT 1
                ");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    error_log("Session found by latest: " . $result['id']);
                    return [
                        'email' => $result['email'],
                        'name' => $result['name'],
                        'role' => $result['role'],
                        'username' => $result['username']
                    ];
                }
                
                error_log("No active session found in database");
                return null;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM sessions 
                WHERE id = ? AND expires_at > NOW()
            ");
            $stmt->execute([$sessionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Session found: $sessionId");
                return [
                    'email' => $result['email'],
                    'name' => $result['name'],
                    'role' => $result['role'],
                    'username' => $result['username']
                ];
            }
            
            error_log("Session not found or expired: $sessionId");
            return null;
        } catch (PDOException $e) {
            error_log("Error getting session: " . $e->getMessage());
            return null;
        }
    }

    public function destroySession($sessionId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            error_log("Session destroyed: $sessionId");
            return true;
        } catch (PDOException $e) {
            error_log("Error destroying session: " . $e->getMessage());
            return false;
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function getConnectionStatus() {
        return [
            'postgresql_connected' => $this->usePostgreSQL,
            'fallback_json' => $this->fallbackToJSON,
            'database_url_configured' => !empty($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL'))
        ];
    }
}