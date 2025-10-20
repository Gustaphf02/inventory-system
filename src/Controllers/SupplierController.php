<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Inventory\Models\Supplier;

class SupplierController
{
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $suppliers = $this->supplierModel->getAll();
            
            return $response->withJson([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener proveedores: ' . $e->getMessage()
            ]);
        }
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $supplier = $this->supplierModel->getById($id);
            
            if (!$supplier) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $supplier
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener proveedor: ' . $e->getMessage()
            ]);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $supplierId = $this->supplierModel->create($data);
            
            return $response->withStatus(201)->withJson([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'data' => ['id' => $supplierId]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al crear proveedor: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $result = $this->supplierModel->update($id, $data);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al actualizar proveedor: ' . $e->getMessage()
            ]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $result = $this->supplierModel->delete($id);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al eliminar proveedor: ' . $e->getMessage()
            ]);
        }
    }

    public function getProducts(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $products = $this->supplierModel->getProducts($id);
            
            return $response->withJson([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener productos del proveedor: ' . $e->getMessage()
            ]);
        }
    }

    public function getMetrics(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $metrics = $this->supplierModel->getPerformanceMetrics($id);
            
            return $response->withJson([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener métricas del proveedor: ' . $e->getMessage()
            ]);
        }
    }
}
