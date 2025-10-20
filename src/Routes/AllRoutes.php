<?php

namespace Inventory\Routes;

use Slim\App;
use Inventory\Controllers\CategoryController;
use Inventory\Controllers\SupplierController;
use Inventory\Controllers\AuthController;
use Inventory\Controllers\ReportController;
use Inventory\Controllers\InventoryController;
use Inventory\Middleware\ValidationMiddleware;

class CategoryRoutes
{
    public static function register($app)
    {
        $app->group('/categories', function ($group) {
            $group->get('', CategoryController::class . ':getAll');
            $group->get('/{id}', CategoryController::class . ':getById');
            $group->post('', CategoryController::class . ':create')
                  ->add(ValidationMiddleware::class . ':validateCategory');
            $group->put('/{id}', CategoryController::class . ':update')
                  ->add(ValidationMiddleware::class . ':validateCategory');
            $group->delete('/{id}', CategoryController::class . ':delete');
            $group->get('/hierarchy/tree', CategoryController::class . ':getHierarchy');
        });
    }
}

class SupplierRoutes
{
    public static function register($app)
    {
        $app->group('/suppliers', function ($group) {
            $group->get('', SupplierController::class . ':getAll');
            $group->get('/{id}', SupplierController::class . ':getById');
            $group->post('', SupplierController::class . ':create')
                  ->add(ValidationMiddleware::class . ':validateSupplier');
            $group->put('/{id}', SupplierController::class . ':update')
                  ->add(ValidationMiddleware::class . ':validateSupplier');
            $group->delete('/{id}', SupplierController::class . ':delete');
            $group->get('/{id}/products', SupplierController::class . ':getProducts');
            $group->get('/{id}/metrics', SupplierController::class . ':getMetrics');
        });
    }
}

class AuthRoutes
{
    public static function register($app)
    {
        $app->group('/auth', function ($group) {
            $group->post('/login', AuthController::class . ':login');
            $group->post('/register', AuthController::class . ':register');
            $group->post('/refresh', AuthController::class . ':refresh');
            $group->post('/logout', AuthController::class . ':logout');
            $group->get('/me', AuthController::class . ':me');
        });
    }
}

class ReportRoutes
{
    public static function register($app)
    {
        $app->group('/reports', function ($group) {
            $group->get('/inventory/summary', ReportController::class . ':getInventorySummary');
            $group->get('/inventory/low-stock', ReportController::class . ':getLowStockReport');
            $group->get('/inventory/value', ReportController::class . ':getInventoryValueReport');
            $group->get('/stock/movements', ReportController::class . ':getStockMovementsReport');
            $group->get('/products/export', ReportController::class . ':exportProducts');
            $group->get('/dashboard/stats', ReportController::class . ':getDashboardStats');
        });
    }
}

class InventoryRoutes
{
    public static function register($app)
    {
        $app->group('/inventory', function ($group) {
            $group->get('/dashboard', InventoryController::class . ':getDashboard');
            $group->get('/search', InventoryController::class . ':search');
            $group->get('/barcode/{sku}', InventoryController::class . ':generateBarcode');
            $group->post('/bulk-update', InventoryController::class . ':bulkUpdate');
            $group->get('/reorder-suggestions', InventoryController::class . ':getReorderSuggestions');
        });
    }
}
