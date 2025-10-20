<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Inventory\Models\Product;
use Inventory\Services\ProductService;
use Inventory\Services\ReportService;
use Inventory\Services\ExternalApiService;

class ProductController
{
    private $productModel;
    private $productService;
    private $reportService;
    private $externalApiService;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->productService = new ProductService();
        $this->reportService = new ReportService();
        $this->externalApiService = new ExternalApiService();
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $products = $this->productModel->getAll($queryParams);
            
            return $response->withJson([
                'success' => true,
                'data' => $products,
                'total' => count($products)
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ]);
        }
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $product = $this->productModel->getById($id);
            
            if (!$product) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener producto: ' . $e->getMessage()
            ]);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validar SKU único
            if ($this->productModel->getBySku($data['sku'])) {
                return $response->withStatus(400)->withJson([
                    'success' => false,
                    'message' => 'El SKU ya existe'
                ]);
            }
            
            $productId = $this->productModel->create($data);
            
            // Registrar movimiento de stock inicial
            $this->productService->recordStockMovement(
                $productId, 
                $data['stock_quantity'], 
                'initial_stock', 
                'Stock inicial'
            );
            
            return $response->withStatus(201)->withJson([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => ['id' => $productId]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al crear producto: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $result = $this->productModel->update($id, $data);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $result = $this->productModel->delete($id);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStock(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $quantity = $data['quantity'];
            $operation = $data['operation'] ?? 'add';
            $reason = $data['reason'] ?? 'Manual adjustment';
            
            $result = $this->productModel->updateStock($id, $quantity, $operation);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            // Registrar movimiento de stock
            $this->productService->recordStockMovement(
                $id, 
                $quantity, 
                $operation, 
                $reason
            );
            
            return $response->withJson([
                'success' => true,
                'message' => 'Stock actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ]);
        }
    }

    public function getLowStock(Request $request, Response $response): Response
    {
        try {
            $products = $this->productModel->getLowStockProducts();
            
            return $response->withJson([
                'success' => true,
                'data' => $products,
                'total' => count($products)
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener productos con stock bajo: ' . $e->getMessage()
            ]);
        }
    }

    public function getStockMovements(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $limit = $request->getQueryParams()['limit'] ?? 50;
            
            $movements = $this->productModel->getStockMovements($id, $limit);
            
            return $response->withJson([
                'success' => true,
                'data' => $movements
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener movimientos de stock: ' . $e->getMessage()
            ]);
        }
    }

    public function getBySku(Request $request, Response $response, array $args): Response
    {
        try {
            $sku = $args['sku'];
            $product = $this->productModel->getBySku($sku);
            
            if (!$product) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al buscar producto: ' . $e->getMessage()
            ]);
        }
    }

    public function import(Request $request, Response $response): Response
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            
            if (empty($uploadedFiles['file'])) {
                return $response->withStatus(400)->withJson([
                    'success' => false,
                    'message' => 'No se proporcionó archivo'
                ]);
            }
            
            $file = $uploadedFiles['file'];
            $result = $this->productService->importFromFile($file);
            
            return $response->withJson([
                'success' => true,
                'message' => 'Importación completada',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en la importación: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $format = $queryParams['format'] ?? 'csv';
            
            $filePath = $this->reportService->exportProducts($queryParams, $format);
            
            return $response->withHeader('Content-Type', 'application/octet-stream')
                           ->withHeader('Content-Disposition', 'attachment; filename="products.' . $format . '"')
                           ->withBody(new \Slim\Psr7\Stream(fopen($filePath, 'r')));
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en la exportación: ' . $e->getMessage()
            ]);
        }
    }

    public function syncExternal(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $provider = $data['provider'] ?? 'mouser';
            $sku = $data['sku'] ?? null;
            
            $result = $this->externalApiService->syncProduct($provider, $sku);
            
            return $response->withJson([
                'success' => true,
                'message' => 'Sincronización completada',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error en la sincronización: ' . $e->getMessage()
            ]);
        }
    }
}
