<?php

namespace Inventory\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Respect\Validation\Validator as v;

class ValidationMiddleware
{
    public function validateProduct(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        
        $errors = [];
        
        // Validar campos requeridos
        if (empty($data['name'])) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (empty($data['sku'])) {
            $errors[] = 'El SKU es requerido';
        } elseif (!v::alnum()->noWhitespace()->validate($data['sku'])) {
            $errors[] = 'El SKU debe contener solo letras y números';
        }
        
        if (!isset($data['price']) || !v::numeric()->min(0)->validate($data['price'])) {
            $errors[] = 'El precio debe ser un número mayor o igual a 0';
        }
        
        if (!isset($data['cost']) || !v::numeric()->min(0)->validate($data['cost'])) {
            $errors[] = 'El costo debe ser un número mayor o igual a 0';
        }
        
        if (!isset($data['stock_quantity']) || !v::intVal()->min(0)->validate($data['stock_quantity'])) {
            $errors[] = 'La cantidad en stock debe ser un número entero mayor o igual a 0';
        }
        
        if (!isset($data['min_stock_level']) || !v::intVal()->min(0)->validate($data['min_stock_level'])) {
            $errors[] = 'El nivel mínimo de stock debe ser un número entero mayor o igual a 0';
        }
        
        if (!empty($data['category_id']) && !v::intVal()->min(1)->validate($data['category_id'])) {
            $errors[] = 'El ID de categoría debe ser un número entero válido';
        }
        
        if (!empty($data['supplier_id']) && !v::intVal()->min(1)->validate($data['supplier_id'])) {
            $errors[] = 'El ID de proveedor debe ser un número entero válido';
        }
        
        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ]));
            
            return $response->withStatus(400)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
    
    public function validateProductUpdate(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        $errors = [];
        
        // Validaciones más flexibles para actualización
        if (isset($data['price']) && !v::numeric()->min(0)->validate($data['price'])) {
            $errors[] = 'El precio debe ser un número mayor o igual a 0';
        }
        
        if (isset($data['cost']) && !v::numeric()->min(0)->validate($data['cost'])) {
            $errors[] = 'El costo debe ser un número mayor o igual a 0';
        }
        
        if (isset($data['stock_quantity']) && !v::intVal()->min(0)->validate($data['stock_quantity'])) {
            $errors[] = 'La cantidad en stock debe ser un número entero mayor o igual a 0';
        }
        
        if (isset($data['min_stock_level']) && !v::intVal()->min(0)->validate($data['min_stock_level'])) {
            $errors[] = 'El nivel mínimo de stock debe ser un número entero mayor o igual a 0';
        }
        
        if (isset($data['category_id']) && !empty($data['category_id']) && !v::intVal()->min(1)->validate($data['category_id'])) {
            $errors[] = 'El ID de categoría debe ser un número entero válido';
        }
        
        if (isset($data['supplier_id']) && !empty($data['supplier_id']) && !v::intVal()->min(1)->validate($data['supplier_id'])) {
            $errors[] = 'El ID de proveedor debe ser un número entero válido';
        }
        
        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ]));
            
            return $response->withStatus(400)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
    
    public function validateCategory(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'El nombre de la categoría es requerido';
        }
        
        if (!empty($data['parent_id']) && !v::intVal()->min(1)->validate($data['parent_id'])) {
            $errors[] = 'El ID de categoría padre debe ser un número entero válido';
        }
        
        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ]));
            
            return $response->withStatus(400)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
    
    public function validateSupplier(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'El nombre del proveedor es requerido';
        }
        
        if (!empty($data['email']) && !v::email()->validate($data['email'])) {
            $errors[] = 'El email debe tener un formato válido';
        }
        
        if (!empty($data['phone']) && !v::phone()->validate($data['phone'])) {
            $errors[] = 'El teléfono debe tener un formato válido';
        }
        
        if (!empty($data['website']) && !v::url()->validate($data['website'])) {
            $errors[] = 'El sitio web debe ser una URL válida';
        }
        
        if (!empty($errors)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ]));
            
            return $response->withStatus(400)
                           ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
}
