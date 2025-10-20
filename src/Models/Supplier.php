<?php

namespace Inventory\Models;

use Inventory\Config\Database;

class Supplier
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT s.*, COUNT(p.id) as product_count 
                FROM suppliers s 
                LEFT JOIN products p ON s.id = p.supplier_id 
                GROUP BY s.id 
                ORDER BY s.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM suppliers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address, 
                city, state, country, postal_code, website, payment_terms, 
                lead_time_days, created_at, updated_at) 
                VALUES (:name, :contact_person, :email, :phone, :address, 
                :city, :state, :country, :postal_code, :website, :payment_terms, 
                :lead_time_days, NOW(), NOW())";
        
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
        
        $sql = "UPDATE suppliers SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        // Verificar si hay productos asociados
        $sql = "SELECT COUNT(*) as count FROM products WHERE supplier_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new \Exception("No se puede eliminar el proveedor porque tiene productos asociados");
        }
        
        $sql = "DELETE FROM suppliers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getProducts($supplierId)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.supplier_id = :supplier_id 
                ORDER BY p.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['supplier_id' => $supplierId]);
        return $stmt->fetchAll();
    }

    public function getPerformanceMetrics($supplierId)
    {
        $sql = "SELECT 
                    COUNT(p.id) as total_products,
                    SUM(p.stock_quantity * p.cost) as total_value,
                    AVG(p.lead_time_days) as avg_lead_time,
                    COUNT(CASE WHEN p.stock_quantity <= p.min_stock_level THEN 1 END) as low_stock_products
                FROM products p 
                WHERE p.supplier_id = :supplier_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['supplier_id' => $supplierId]);
        return $stmt->fetch();
    }
}
