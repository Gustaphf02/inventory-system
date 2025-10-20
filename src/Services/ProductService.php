<?php

namespace Inventory\Services;

use Inventory\Config\Database;
use Inventory\Models\Product;

class ProductService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function recordStockMovement($productId, $quantity, $movementType, $reason, $referenceNumber = null)
    {
        $sql = "INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, reason, reference_number) 
                VALUES (:product_id, :user_id, :movement_type, :quantity, :reason, :reference_number)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'product_id' => $productId,
            'user_id' => $_SESSION['user_id'] ?? 1, // TODO: Obtener del token JWT
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'reason' => $reason,
            'reference_number' => $referenceNumber
        ]);
    }

    public function importFromFile($file)
    {
        $filePath = $file->getStream()->getMetadata('uri');
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        
        $results = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'details' => []
        ];

        try {
            if ($extension === 'csv') {
                $this->importFromCSV($filePath, $results);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $this->importFromExcel($filePath, $results);
            } else {
                throw new \Exception('Formato de archivo no soportado');
            }
        } catch (\Exception $e) {
            $results['errors']++;
            $results['details'][] = 'Error general: ' . $e->getMessage();
        }

        return $results;
    }

    private function importFromCSV($filePath, &$results)
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, $data);
            
            try {
                // Validar datos requeridos
                if (empty($row['sku']) || empty($row['name'])) {
                    $results['skipped']++;
                    $results['details'][] = "Fila saltada: SKU o nombre faltante";
                    continue;
                }
                
                // Verificar si el producto ya existe
                $productModel = new Product();
                if ($productModel->getBySku($row['sku'])) {
                    $results['skipped']++;
                    $results['details'][] = "SKU {$row['sku']} ya existe";
                    continue;
                }
                
                // Preparar datos para inserción
                $productData = [
                    'name' => $row['name'],
                    'sku' => $row['sku'],
                    'description' => $row['description'] ?? '',
                    'price' => floatval($row['price'] ?? 0),
                    'cost' => floatval($row['cost'] ?? 0),
                    'stock_quantity' => intval($row['stock_quantity'] ?? 0),
                    'min_stock_level' => intval($row['min_stock_level'] ?? 0),
                    'category_id' => $row['category_id'] ?? null,
                    'supplier_id' => $row['supplier_id'] ?? null,
                    'weight' => floatval($row['weight'] ?? 0),
                    'dimensions' => $row['dimensions'] ?? ''
                ];
                
                $productId = $productModel->create($productData);
                
                // Registrar movimiento de stock inicial
                if ($productData['stock_quantity'] > 0) {
                    $this->recordStockMovement(
                        $productId, 
                        $productData['stock_quantity'], 
                        'initial_stock', 
                        'Importación inicial'
                    );
                }
                
                $results['success']++;
                $results['details'][] = "Producto {$row['sku']} importado exitosamente";
                
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = "Error en SKU {$row['sku']}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
    }

    private function importFromExcel($filePath, &$results)
    {
        // Implementar importación desde Excel usando PhpSpreadsheet
        // Por ahora, lanzar excepción para indicar que no está implementado
        throw new \Exception('Importación desde Excel no implementada aún');
    }

    public function generateBarcode($sku)
    {
        // Generar código de barras usando una librería como Picqer\Barcode
        // Por ahora, retornar un placeholder
        return "BARCODE_{$sku}";
    }

    public function calculateReorderPoint($productId)
    {
        $sql = "SELECT 
                    AVG(sm.quantity) as avg_daily_usage,
                    p.lead_time_days,
                    p.min_stock_level
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                WHERE sm.product_id = :product_id 
                AND sm.movement_type = 'out'
                AND sm.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY p.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return 0;
        }
        
        $avgDailyUsage = $data['avg_daily_usage'] ?? 0;
        $leadTimeDays = $data['lead_time_days'] ?? 7;
        $minStockLevel = $data['min_stock_level'] ?? 0;
        
        // Fórmula: (Uso promedio diario × Tiempo de entrega) + Stock mínimo
        return ($avgDailyUsage * $leadTimeDays) + $minStockLevel;
    }

    public function getInventoryValue()
    {
        $sql = "SELECT 
                    SUM(stock_quantity * cost) as total_cost_value,
                    SUM(stock_quantity * price) as total_selling_value,
                    COUNT(*) as total_products,
                    SUM(CASE WHEN stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_count
                FROM products 
                WHERE is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getTopProducts($limit = 10, $period = 30)
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.sku,
                    SUM(sm.quantity) as total_movement,
                    COUNT(sm.id) as movement_count
                FROM products p
                JOIN stock_movements sm ON p.id = sm.product_id
                WHERE sm.movement_type = 'out'
                AND sm.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
                GROUP BY p.id
                ORDER BY total_movement DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['period' => $period, 'limit' => $limit]);
        return $stmt->fetchAll();
    }
}
