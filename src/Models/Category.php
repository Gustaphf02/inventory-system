<?php

namespace Inventory\Models;

use Inventory\Config\Database;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO categories (name, description, parent_id, created_at, updated_at) 
                VALUES (:name, :description, :parent_id, NOW(), NOW())";
        
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
        
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        // Verificar si hay productos asociados
        $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new \Exception("No se puede eliminar la categoría porque tiene productos asociados");
        }
        
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getHierarchy()
    {
        $sql = "SELECT * FROM categories ORDER BY parent_id, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        return $this->buildHierarchy($categories);
    }

    private function buildHierarchy($categories, $parentId = null)
    {
        $result = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildHierarchy($categories, $category['id']);
                $result[] = $category;
            }
        }
        return $result;
    }
}
