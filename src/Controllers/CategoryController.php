<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Inventory\Models\Category;

class CategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $categories = $this->categoryModel->getAll();
            
            return $response->withJson([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ]);
        }
    }

    public function getById(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $category = $this->categoryModel->getById($id);
            
            if (!$category) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $categoryId = $this->categoryModel->create($data);
            
            return $response->withStatus(201)->withJson([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => ['id' => $categoryId]
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al crear categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $result = $this->categoryModel->update($id, $data);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al actualizar categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $result = $this->categoryModel->delete($id);
            
            if (!$result) {
                return $response->withStatus(404)->withJson([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }
            
            return $response->withJson([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al eliminar categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function getHierarchy(Request $request, Response $response): Response
    {
        try {
            $hierarchy = $this->categoryModel->getHierarchy();
            
            return $response->withJson([
                'success' => true,
                'data' => $hierarchy
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener jerarquía de categorías: ' . $e->getMessage()
            ]);
        }
    }
}
