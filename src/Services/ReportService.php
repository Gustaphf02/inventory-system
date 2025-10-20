<?php

namespace Inventory\Services;

use Inventory\Config\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use TCPDF;

class ReportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function exportProducts($filters = [], $format = 'csv')
    {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
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
        
        $sql .= " ORDER BY p.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        $filename = 'products_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filepath = $_ENV['UPLOAD_PATH'] . 'exports/' . $filename;
        
        // Crear directorio si no existe
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if ($format === 'csv') {
            $this->exportToCSV($products, $filepath);
        } elseif ($format === 'xlsx') {
            $this->exportToExcel($products, $filepath);
        } elseif ($format === 'pdf') {
            $this->exportToPDF($products, $filepath);
        }
        
        return $filepath;
    }

    private function exportToCSV($products, $filepath)
    {
        $handle = fopen($filepath, 'w');
        
        // Headers
        fputcsv($handle, [
            'ID', 'SKU', 'Nombre', 'Descripción', 'Precio', 'Costo', 
            'Stock', 'Stock Mínimo', 'Categoría', 'Proveedor', 'Peso', 'Dimensiones'
        ]);
        
        // Data
        foreach ($products as $product) {
            fputcsv($handle, [
                $product['id'],
                $product['sku'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['cost'],
                $product['stock_quantity'],
                $product['min_stock_level'],
                $product['category_name'],
                $product['supplier_name'],
                $product['weight'],
                $product['dimensions']
            ]);
        }
        
        fclose($handle);
    }

    private function exportToExcel($products, $filepath)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = [
            'A1' => 'ID', 'B1' => 'SKU', 'C1' => 'Nombre', 'D1' => 'Descripción',
            'E1' => 'Precio', 'F1' => 'Costo', 'G1' => 'Stock', 'H1' => 'Stock Mínimo',
            'I1' => 'Categoría', 'J1' => 'Proveedor', 'K1' => 'Peso', 'L1' => 'Dimensiones'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Data
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product['id']);
            $sheet->setCellValue('B' . $row, $product['sku']);
            $sheet->setCellValue('C' . $row, $product['name']);
            $sheet->setCellValue('D' . $row, $product['description']);
            $sheet->setCellValue('E' . $row, $product['price']);
            $sheet->setCellValue('F' . $row, $product['cost']);
            $sheet->setCellValue('G' . $row, $product['stock_quantity']);
            $sheet->setCellValue('H' . $row, $product['min_stock_level']);
            $sheet->setCellValue('I' . $row, $product['category_name']);
            $sheet->setCellValue('J' . $row, $product['supplier_name']);
            $sheet->setCellValue('K' . $row, $product['weight']);
            $sheet->setCellValue('L' . $row, $product['dimensions']);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
    }

    private function exportToPDF($products, $filepath)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Inventario');
        $pdf->SetTitle('Reporte de Productos');
        $pdf->SetSubject('Inventario de Productos');
        
        $pdf->AddPage();
        
        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'REPORTE DE PRODUCTOS', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Table headers
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(15, 8, 'ID', 1, 0, 'C');
        $pdf->Cell(25, 8, 'SKU', 1, 0, 'C');
        $pdf->Cell(50, 8, 'Nombre', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Precio', 1, 0, 'C');
        $pdf->Cell(20, 8, 'Stock', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Categoría', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Proveedor', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('helvetica', '', 7);
        foreach ($products as $product) {
            $pdf->Cell(15, 6, $product['id'], 1, 0, 'C');
            $pdf->Cell(25, 6, $product['sku'], 1, 0, 'C');
            $pdf->Cell(50, 6, substr($product['name'], 0, 30), 1, 0, 'L');
            $pdf->Cell(20, 6, '$' . number_format($product['price'], 2), 1, 0, 'R');
            $pdf->Cell(20, 6, $product['stock_quantity'], 1, 0, 'C');
            $pdf->Cell(30, 6, substr($product['category_name'] ?? '', 0, 20), 1, 0, 'L');
            $pdf->Cell(30, 6, substr($product['supplier_name'] ?? '', 0, 20), 1, 1, 'L');
        }
        
        $pdf->Output($filepath, 'F');
    }

    public function generateInventoryReport($type = 'summary')
    {
        switch ($type) {
            case 'summary':
                return $this->getInventorySummary();
            case 'low_stock':
                return $this->getLowStockReport();
            case 'value':
                return $this->getInventoryValueReport();
            case 'movements':
                return $this->getStockMovementsReport();
            default:
                throw new \Exception('Tipo de reporte no válido');
        }
    }

    private function getInventorySummary()
    {
        $sql = "SELECT 
                    COUNT(*) as total_products,
                    SUM(stock_quantity) as total_stock,
                    SUM(stock_quantity * cost) as total_cost_value,
                    SUM(stock_quantity * price) as total_selling_value,
                    SUM(CASE WHEN stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_products,
                    AVG(stock_quantity) as avg_stock_per_product
                FROM products 
                WHERE is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    private function getLowStockReport()
    {
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE p.stock_quantity <= p.min_stock_level 
                AND p.is_active = 1
                ORDER BY (p.stock_quantity - p.min_stock_level) ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getInventoryValueReport()
    {
        $sql = "SELECT 
                    c.name as category_name,
                    COUNT(p.id) as product_count,
                    SUM(p.stock_quantity) as total_stock,
                    SUM(p.stock_quantity * p.cost) as total_cost_value,
                    SUM(p.stock_quantity * p.price) as total_selling_value
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1
                GROUP BY c.id, c.name
                ORDER BY total_cost_value DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getStockMovementsReport($days = 30)
    {
        $sql = "SELECT 
                    p.name as product_name,
                    p.sku,
                    sm.movement_type,
                    SUM(sm.quantity) as total_quantity,
                    COUNT(sm.id) as movement_count,
                    u.first_name,
                    u.last_name
                FROM stock_movements sm
                JOIN products p ON sm.product_id = p.id
                LEFT JOIN users u ON sm.user_id = u.id
                WHERE sm.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY p.id, sm.movement_type
                ORDER BY total_quantity DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll();
    }
}
