<?php
require_once __DIR__ . '/.auth.php';
/**
 * Sistema de Inventario - Página de Prueba
 * Esta página muestra datos de ejemplo sin necesidad de base de datos
 */

// Datos de ejemplo
$sampleProducts = [
    [
        'id' => 1,
        'sku' => 'RES-1K-1/4W',
        'name' => 'Resistor 1K Ohm 1/4W',
        'description' => 'Resistor de carbón 1K Ohm, 1/4 Watt, tolerancia 5%',
        'price' => 0.10,
        'cost' => 0.05,
        'stock_quantity' => 1000,
        'min_stock_level' => 100,
        'category_name' => 'Resistores',
        'supplier_name' => 'Mouser Electronics'
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
        'category_name' => 'Capacitores',
        'supplier_name' => 'Mouser Electronics'
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
        'category_name' => 'Semiconductores',
        'supplier_name' => 'DigiKey'
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
        'category_name' => 'Semiconductores',
        'supplier_name' => 'DigiKey'
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
        'category_name' => 'Conectores',
        'supplier_name' => 'Newark'
    ]
];

$sampleCategories = [
    ['id' => 1, 'name' => 'Electrónicos', 'product_count' => 8],
    ['id' => 2, 'name' => 'Semiconductores', 'product_count' => 2],
    ['id' => 3, 'name' => 'Resistores', 'product_count' => 1],
    ['id' => 4, 'name' => 'Capacitores', 'product_count' => 1],
    ['id' => 5, 'name' => 'Conectores', 'product_count' => 1],
    ['id' => 6, 'name' => 'Herramientas', 'product_count' => 1],
    ['id' => 7, 'name' => 'Cables', 'product_count' => 1],
    ['id' => 8, 'name' => 'Placas PCB', 'product_count' => 1]
];

$sampleSuppliers = [
    ['id' => 1, 'name' => 'Mouser Electronics', 'product_count' => 2],
    ['id' => 2, 'name' => 'DigiKey', 'product_count' => 2],
    ['id' => 3, 'name' => 'Newark', 'product_count' => 1],
    ['id' => 4, 'name' => 'RS Components', 'product_count' => 0]
];

$stats = [
    'totalProducts' => count($sampleProducts),
    'totalValue' => array_sum(array_map(function($p) { return $p['stock_quantity'] * $p['cost']; }, $sampleProducts)),
    'lowStockProducts' => count(array_filter($sampleProducts, function($p) { return $p['stock_quantity'] <= $p['min_stock_level']; })),
    'totalSuppliers' => count($sampleSuppliers)
];

// Simular API endpoints
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['api']) {
        case 'products':
            echo json_encode(['success' => true, 'data' => $sampleProducts]);
            break;
        case 'categories':
            echo json_encode(['success' => true, 'data' => $sampleCategories]);
            break;
        case 'suppliers':
            echo json_encode(['success' => true, 'data' => $sampleSuppliers]);
            break;
        case 'dashboard-stats':
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-warehouse me-3"></i>
                        Sistema de Inventario
                    </h1>
                    <p class="lead mb-4">
                        Sistema completo de gestión de inventario inspirado en Mouser Electronics. 
                        Desarrollado con PHP y Vue.js para máxima eficiencia y escalabilidad.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#demo" class="btn btn-light btn-lg">
                            <i class="fas fa-play me-2"></i>
                            Ver Demo
                        </a>
                        <a href="config.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-cog me-2"></i>
                            Configuración
                        </a>
                        <a href="users.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-users me-2"></i>
                            Usuarios
                        </a>
                        <a href="maintenance.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-tools me-2"></i>
                            Mantenimiento
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-chart-line fa-10x opacity-75"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Características Principales</h2>
                    <p class="lead text-muted">Todo lo que necesitas para gestionar tu inventario</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-boxes fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Gestión de Productos</h5>
                            <p class="card-text text-muted">
                                CRUD completo de productos con SKU único, categorización jerárquica y control de stock avanzado.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Reportes Avanzados</h5>
                            <p class="card-text text-muted">
                                Dashboard interactivo, reportes en PDF/Excel y análisis de valor en tiempo real.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-truck fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Gestión de Proveedores</h5>
                            <p class="card-text text-muted">
                                Integración con APIs externas (Mouser, DigiKey, Newark) y métricas de rendimiento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Demo en Vivo</h2>
                    <p class="lead text-muted">Explora las funcionalidades del sistema</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-5">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Total Productos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['totalProducts'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Valor Total
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold">$<?= number_format($stats['totalValue'], 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Stock Bajo
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['lowStockProducts'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Proveedores
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= $stats['totalSuppliers'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Productos de Ejemplo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Proveedor</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sampleProducts as $product): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                                    <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                                    <td>
                                        <span class="badge <?= $product['stock_quantity'] <= $product['min_stock_level'] ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $product['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $product['stock_quantity'] <= $product['min_stock_level'] ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $product['stock_quantity'] <= $product['min_stock_level'] ? 'Stock Bajo' : 'En Stock' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Stack Tecnológico</h2>
                    <p class="lead text-muted">Tecnologías modernas para máxima eficiencia</p>
                </div>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fab fa-php fa-4x text-primary mb-3"></i>
                        <h5>PHP 8.1+</h5>
                        <p class="text-muted">Backend robusto con Slim Framework</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fab fa-vuejs fa-4x text-success mb-3"></i>
                        <h5>Vue.js 3</h5>
                        <p class="text-muted">Frontend reactivo y moderno</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-database fa-4x text-warning mb-3"></i>
                        <h5>MySQL</h5>
                        <p class="text-muted">Base de datos relacional</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4">
                        <i class="fas fa-shield-alt fa-4x text-danger mb-3"></i>
                        <h5>JWT</h5>
                        <p class="text-muted">Autenticación segura</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistema de Inventario</h5>
                    <p class="text-muted">Desarrollado con ❤️ para la gestión eficiente de inventarios</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        © 2024 Sistema de Inventario. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
