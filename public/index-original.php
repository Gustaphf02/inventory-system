<?php
/**
 * Sistema de Inventario - Configuración Principal
 * Inspirado en Mouser Electronics
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ErrorMiddleware;
use Inventory\Middleware\CorsMiddleware;
use Inventory\Middleware\AuthMiddleware;
use Inventory\Config\Database;
use Inventory\Routes\ProductRoutes;
use Inventory\Routes\CategoryRoutes;
use Inventory\Routes\SupplierRoutes;
use Inventory\Routes\AuthRoutes;
use Inventory\Routes\ReportRoutes;
use Inventory\Routes\InventoryRoutes;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Crear aplicación Slim
$app = AppFactory::create();

// Middleware global
$app->add(new BodyParsingMiddleware());
$app->add(new CorsMiddleware());

// Configurar manejo de errores
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Inicializar base de datos
Database::init();

// Rutas de autenticación (sin middleware de auth)
$app->group('/api/auth', function ($group) {
    AuthRoutes::register($group);
});

// Rutas protegidas (con middleware de auth)
$app->group('/api', function ($group) {
    ProductRoutes::register($group);
    CategoryRoutes::register($group);
    SupplierRoutes::register($group);
    ReportRoutes::register($group);
    InventoryRoutes::register($group);
})->add(new AuthMiddleware());

// Ruta de salud
$app->get('/health', function ($request, $response) {
    return $response->withJson([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]);
});

// Ejecutar aplicación
$app->run();
