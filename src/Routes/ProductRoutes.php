<?php

namespace Inventory\Routes;

use Slim\App;
use Inventory\Controllers\ProductController;
use Inventory\Middleware\ValidationMiddleware;

class ProductRoutes
{
    public static function register($app)
    {
        $app->group('/products', function ($group) {
            // Obtener todos los productos con filtros
            $group->get('', ProductController::class . ':getAll');
            
            // Obtener producto por ID
            $group->get('/{id}', ProductController::class . ':getById');
            
            // Crear nuevo producto
            $group->post('', ProductController::class . ':create')
                  ->add(ValidationMiddleware::class . ':validateProduct');
            
            // Actualizar producto
            $group->put('/{id}', ProductController::class . ':update')
                  ->add(ValidationMiddleware::class . ':validateProductUpdate');
            
            // Eliminar producto
            $group->delete('/{id}', ProductController::class . ':delete');
            
            // Actualizar stock
            $group->patch('/{id}/stock', ProductController::class . ':updateStock');
            
            // Obtener productos con stock bajo
            $group->get('/low-stock', ProductController::class . ':getLowStock');
            
            // Obtener movimientos de stock
            $group->get('/{id}/movements', ProductController::class . ':getStockMovements');
            
            // Buscar productos por SKU
            $group->get('/sku/{sku}', ProductController::class . ':getBySku');
            
            // Importar productos desde CSV/Excel
            $group->post('/import', ProductController::class . ':import');
            
            // Exportar productos a CSV/Excel
            $group->get('/export', ProductController::class . ':export');
            
            // Sincronizar con APIs externas (Mouser, DigiKey, etc.)
            $group->post('/sync-external', ProductController::class . ':syncExternal');
        });
    }
}
