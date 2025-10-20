<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Inventory\Services\ProductService;
use Inventory\Services\ExternalApiService;

class InventoryController
{
    private $productService;
    private $externalApiService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->externalApiService = new ExternalApiService();
    }

    public function getDashboard(Request $request, Response $response): Response
    {
        try {
            $stats = $this->productService->getInventoryValue();
            $topProducts = $this->productService->getTopProducts(5);
            $lowStockProducts = $this->productService->getLowStockProducts();
            
            return $response->withJson([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'topProducts' => $topProducts,
                    'lowStockProducts' => $lowStockProducts
                ]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request, Response $response): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $provider = $request->getQueryParams()['provider'] ?? 'mouser';
            
            if (empty($query)) {
                return $response->withStatus(400)->withJson([
                    'success' => false,
                    'message' => 'Parámetro de búsqueda requerido'
                ]);
            }
            
            $results = $this->externalApiService->searchProduct($provider, $query);
            
            return $response->withJson([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ]);
        }
    }

    public function generateBarcode(Request $request, Response $response, array $args): Response
    {
        try {
            $sku = $args['sku'];
            $barcode = $this->productService->generateBarcode($sku);
            
            return $response->withJson([
                'success' => true,
                'data' => ['barcode' => $barcode]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al generar código de barras: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkUpdate(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $updates = $data['updates'] ?? [];
            
            $results = [
                'success' => 0,
                'errors' => 0,
                'details' => []
            ];
            
            foreach ($updates as $update) {
                try {
                    $productModel = new \Inventory\Models\Product();
                    $result = $productModel->update($update['id'], $update['data']);
                    
                    if ($result) {
                        $results['success']++;
                        $results['details'][] = "Producto {$update['id']} actualizado exitosamente";
                    } else {
                        $results['errors']++;
                        $results['details'][] = "Producto {$update['id']} no encontrado";
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['details'][] = "Error en producto {$update['id']}: " . $e->getMessage();
                }
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en actualización masiva: ' . $e->getMessage()
            ]);
        }
    }

    public function getReorderSuggestions(Request $request, Response $response): Response
    {
        try {
            $productModel = new \Inventory\Models\Product();
            $lowStockProducts = $productModel->getLowStockProducts();
            
            $suggestions = [];
            foreach ($lowStockProducts as $product) {
                $reorderPoint = $this->productService->calculateReorderPoint($product['id']);
                $suggestions[] = [
                    'product' => $product,
                    'reorderPoint' => $reorderPoint,
                    'suggestedQuantity' => max($reorderPoint - $product['stock_quantity'], $product['min_stock_level'])
                ];
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener sugerencias de reorden: ' . $e->getMessage()
            ]);
        }
    }
}
