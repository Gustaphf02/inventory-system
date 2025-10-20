<?php

namespace Inventory\Models;

use Inventory\Config\Database;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['supplier_id'])) {
            $sql .= " AND p.supplier_id = :supplier_id";
            $params['supplier_id'] = $filters['supplier_id'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['in_stock'])) {
            $sql .= " AND p.stock_quantity > 0";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$filters['limit'];
        }
        
        if (!empty($filters['offset'])) {
            $sql .= " OFFSET :offset";
            $params['offset'] = (int)$filters['offset'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getBySku($sku)
    {
        $sql = "SELECT * FROM products WHERE sku = :sku";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sku' => $sku]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO products (name, sku, description, price, cost, stock_quantity, 
                min_stock_level, category_id, supplier_id, specifications, image_url, 
                weight, dimensions, created_at, updated_at) 
                VALUES (:name, :sku, :description, :price, :cost, :stock_quantity, 
                :min_stock_level, :category_id, :supplier_id, :specifications, :image_url, 
                :weight, :dimensions, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields[] = "updated_at = NOW()";
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function updateStock($id, $quantity, $operation = 'add')
    {
        $sql = $operation === 'add' 
            ? "UPDATE products SET stock_quantity = stock_quantity + :quantity WHERE id = :id"
            : "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'quantity' => $quantity]);
    }

    public function getLowStockProducts()
    {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE p.stock_quantity <= p.min_stock_level";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalValue()
    {
        $sql = "SELECT SUM(stock_quantity * cost) as total_value FROM products";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total_value'] ?? 0;
    }

    public function getStockMovements($productId, $limit = 50)
    {
        $sql = "SELECT sm.*, u.name as user_name 
                FROM stock_movements sm 
                LEFT JOIN users u ON sm.user_id = u.id 
                WHERE sm.product_id = :product_id 
                ORDER BY sm.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId, 'limit' => $limit]);
        return $stmt->fetchAll();
    }
}
