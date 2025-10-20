<?php

namespace Inventory\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Inventory\Services\ReportService;

class ReportController
{
    private $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function getInventorySummary(Request $request, Response $response): Response
    {
        try {
            $summary = $this->reportService->generateInventoryReport('summary');
            
            return $response->withJson([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al generar resumen de inventario: ' . $e->getMessage()
            ]);
        }
    }

    public function getLowStockReport(Request $request, Response $response): Response
    {
        try {
            $report = $this->reportService->generateInventoryReport('low_stock');
            
            return $response->withJson([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al generar reporte de stock bajo: ' . $e->getMessage()
            ]);
        }
    }

    public function getInventoryValueReport(Request $request, Response $response): Response
    {
        try {
            $report = $this->reportService->generateInventoryReport('value');
            
            return $response->withJson([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al generar reporte de valor: ' . $e->getMessage()
            ]);
        }
    }

    public function getStockMovementsReport(Request $request, Response $response): Response
    {
        try {
            $days = $request->getQueryParams()['days'] ?? 30;
            $report = $this->reportService->generateInventoryReport('movements');
            
            return $response->withJson([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al generar reporte de movimientos: ' . $e->getMessage()
            ]);
        }
    }

    public function exportProducts(Request $request, Response $response): Response
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

    public function getDashboardStats(Request $request, Response $response): Response
    {
        try {
            $summary = $this->reportService->generateInventoryReport('summary');
            
            // Agregar estadísticas adicionales para el dashboard
            $stats = [
                'totalProducts' => $summary['total_products'] ?? 0,
                'totalValue' => $summary['total_cost_value'] ?? 0,
                'lowStockProducts' => $summary['low_stock_products'] ?? 0,
                'totalSuppliers' => 4, // Hardcoded por ahora
                'avgStockPerProduct' => $summary['avg_stock_per_product'] ?? 0
            ];
            
            return $response->withJson([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'message' => 'Error al obtener estadísticas del dashboard: ' . $e->getMessage()
            ]);
        }
    }
}
