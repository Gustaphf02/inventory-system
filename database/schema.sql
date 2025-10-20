-- Sistema de Inventario - Base de Datos
-- Inspirado en Mouser Electronics

CREATE DATABASE IF NOT EXISTS inventory_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_system;

-- Tabla de usuarios
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'manager', 'employee', 'viewer') DEFAULT 'employee',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_name (name)
);

-- Tabla de proveedores
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    website VARCHAR(255),
    payment_terms VARCHAR(100),
    lead_time_days INT DEFAULT 7,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_email (email)
);

-- Tabla de productos
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 0,
    max_stock_level INT DEFAULT 1000,
    category_id INT,
    supplier_id INT,
    specifications JSON,
    image_url VARCHAR(255),
    weight DECIMAL(8,3),
    dimensions VARCHAR(100),
    lead_time_days INT DEFAULT 7,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_sku (sku),
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_stock (stock_quantity),
    INDEX idx_price (price)
);

-- Tabla de movimientos de stock
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    movement_type ENUM('in', 'out', 'adjustment', 'transfer', 'initial_stock') NOT NULL,
    quantity INT NOT NULL,
    reason VARCHAR(255),
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (created_at)
);

-- Tabla de órdenes de compra
CREATE TABLE purchase_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('draft', 'pending', 'approved', 'ordered', 'received', 'cancelled') DEFAULT 'draft',
    total_amount DECIMAL(10,2) DEFAULT 0,
    order_date DATE,
    expected_delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order_number (order_number),
    INDEX idx_supplier (supplier_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
);

-- Tabla de items de órdenes de compra
CREATE TABLE purchase_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    received_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_purchase_order (purchase_order_id),
    INDEX idx_product (product_id)
);

-- Tabla de reportes
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('inventory', 'sales', 'purchases', 'stock_movements', 'custom') NOT NULL,
    parameters JSON,
    file_path VARCHAR(255),
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type (type),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
);

-- Tabla de configuración del sistema
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuración inicial
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('company_name', 'Sistema de Inventario', 'Nombre de la empresa'),
('currency', 'USD', 'Moneda por defecto'),
('low_stock_threshold', '10', 'Umbral de stock bajo'),
('auto_reorder', 'false', 'Reorden automático'),
('email_notifications', 'true', 'Notificaciones por email'),
('backup_frequency', 'daily', 'Frecuencia de respaldos');

-- Insertar usuario administrador por defecto
INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('admin', 'admin@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin');

-- Insertar categorías de ejemplo
INSERT INTO categories (name, description) VALUES
('Electrónicos', 'Componentes electrónicos y dispositivos'),
('Semiconductores', 'Transistores, diodos, circuitos integrados'),
('Resistores', 'Resistores de varios tipos y valores'),
('Capacitores', 'Capacitores cerámicos, electrolíticos, etc.'),
('Conectores', 'Conectores y terminales'),
('Herramientas', 'Herramientas de electrónica'),
('Cables', 'Cables y alambres'),
('Placas PCB', 'Placas de circuito impreso');

-- Insertar proveedores de ejemplo
INSERT INTO suppliers (name, contact_person, email, phone, address, city, state, country, website, payment_terms, lead_time_days) VALUES
('Mouser Electronics', 'John Smith', 'sales@mouser.com', '+1-800-346-6873', '1000 North Main Street', 'Mansfield', 'TX', 'USA', 'https://www.mouser.com', 'Net 30', 3),
('DigiKey', 'Jane Doe', 'sales@digikey.com', '+1-800-344-4539', '701 Brooks Avenue South', 'Thief River Falls', 'MN', 'USA', 'https://www.digikey.com', 'Net 30', 2),
('Newark', 'Bob Johnson', 'sales@newark.com', '+1-800-463-9275', '4801 North Ravenswood Avenue', 'Chicago', 'IL', 'USA', 'https://www.newark.com', 'Net 30', 5),
('RS Components', 'Alice Brown', 'sales@rs-components.com', '+44-800-240-240', 'Birchington Road', 'Corby', 'Northamptonshire', 'UK', 'https://www.rs-components.com', 'Net 30', 7);

-- Insertar productos de ejemplo
INSERT INTO products (name, sku, description, price, cost, stock_quantity, min_stock_level, category_id, supplier_id, specifications, weight, dimensions) VALUES
('Resistor 1K Ohm 1/4W', 'RES-1K-1/4W', 'Resistor de carbón 1K Ohm, 1/4 Watt, tolerancia 5%', 0.10, 0.05, 1000, 100, 3, 1, '{"tolerance": "5%", "power_rating": "0.25W", "temperature_coefficient": "100ppm/°C"}', 0.5, '6.3x2.5mm'),
('Capacitor Cerámico 100nF', 'CAP-100NF-50V', 'Capacitor cerámico 100nF, 50V, X7R', 0.15, 0.08, 500, 50, 4, 1, '{"voltage_rating": "50V", "dielectric": "X7R", "tolerance": "10%"}', 0.3, '5x2.5mm'),
('LED Rojo 5mm', 'LED-RED-5MM', 'LED rojo 5mm, 20mA, 2.1V', 0.25, 0.12, 200, 25, 2, 2, '{"forward_voltage": "2.1V", "forward_current": "20mA", "wavelength": "630nm"}', 0.2, '5mm'),
('Transistor NPN BC547', 'TRANS-BC547', 'Transistor NPN BC547, 45V, 100mA', 0.30, 0.15, 150, 20, 2, 2, '{"voltage_rating": "45V", "current_rating": "100mA", "gain": "110-800"}', 0.4, 'TO-92'),
('Conector USB-A', 'CONN-USB-A', 'Conector USB-A hembra, montaje PCB', 1.50, 0.75, 75, 10, 5, 3, '{"connector_type": "USB-A", "mounting": "PCB", "contacts": "4"}', 2.0, '12x4.5mm'),
('Cable Jumper 20cm', 'CABLE-JUMPER-20CM', 'Cable jumper macho-macho, 20cm', 0.50, 0.25, 300, 50, 7, 3, '{"length": "20cm", "connectors": "male-male", "wire_gauge": "22AWG"}', 5.0, '20cm'),
('Placa PCB 5x7cm', 'PCB-5X7CM', 'Placa PCB perforada 5x7cm, 2.54mm pitch', 2.00, 1.00, 50, 5, 8, 4, '{"size": "5x7cm", "hole_pitch": "2.54mm", "holes": "924"}', 15.0, '50x70mm'),
('Multímetro Digital', 'TOOL-MULTIMETER', 'Multímetro digital básico, 3.5 dígitos', 25.00, 12.50, 10, 2, 6, 4, '{"display": "3.5 digits", "voltage_range": "200mV-1000V", "current_range": "200μA-10A"}', 300.0, '180x85x40mm');
