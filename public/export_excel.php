<?php
session_start();
require_once __DIR__ . '/.auth.php';
require_once __DIR__ . '/DatabaseManager.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

try {
    // Verificar si PhpSpreadsheet está disponible y cargar autoload
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        // Intentar cargar PhpSpreadsheet usando Composer autoload
        // Buscar en la raíz del proyecto (un nivel arriba de public/)
        $autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        } else {
            // Intentar en public/ por si está ahí
            $autoloadPath2 = __DIR__ . '/vendor/autoload.php';
            if (file_exists($autoloadPath2)) {
                require_once $autoloadPath2;
            } else {
                throw new Exception('PhpSpreadsheet no está instalado. Por favor, ejecuta desde la raíz del proyecto: composer require phpoffice/phpspreadsheet');
            }
        }
    }
    
    // Obtener los datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['data']) || !is_array($input['data'])) {
        throw new Exception('No se proporcionaron datos para exportar');
    }
    
    $data = $input['data'];
    $filename = $input['filename'] ?? 'Equipos_Existentes';
    
    // Ruta del template
    $templatePath = __DIR__ . '/formato.xlsx';
    
    if (!file_exists($templatePath)) {
        throw new Exception('El archivo template formato.xlsx no existe');
    }
    
    // Cargar el template
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Buscar la fila donde están los encabezados de datos (buscar "Cantidad")
    $dataStartRow = 15; // Por defecto
    $highestRow = $worksheet->getHighestRow();
    $highestCol = $worksheet->getHighestColumn();
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellValue = $worksheet->getCell('A' . $row)->getValue();
        if ($cellValue && strtolower(trim($cellValue)) === 'cantidad') {
            $dataStartRow = $row;
            break;
        }
    }
    
    // Obtener los encabezados de la fila de datos
    $headers = [];
    for ($col = 'A'; $col <= $highestCol; $col++) {
        $cellValue = $worksheet->getCell($col . $dataStartRow)->getValue();
        if ($cellValue) {
            $headers[$col] = trim($cellValue);
        }
    }
    
    // Mapeo de columnas del template a nuestras claves de datos
    $columnMapping = [
        'Cantidad' => 'Cantidad',
        'Especificaciones/EXT.' => 'Especificaciones/EXT',
        'Especificaciones/EXT' => 'Especificaciones/EXT',
        'Sub-Especific.' => 'Sub-Especific',
        'Sub-Especific' => 'Sub-Especific',
        'Asig. Numerica' => 'Asig. Numérica',
        'Asig. Numérica' => 'Asig. Numérica',
        'Descripción' => 'Descripción',
        'Marca' => 'Marca',
        'Modelo' => 'Modelo',
        'Serie' => 'Serie',
        'Estado' => 'Estado'
    ];
    
    // Insertar los datos
    $currentRow = $dataStartRow + 1;
    foreach ($data as $rowData) {
        foreach ($headers as $col => $headerName) {
            // Normalizar el nombre del header (quitar puntos, espacios, tildes)
            $normalizedHeader = strtolower(str_replace(['.', ' ', 'á', 'é', 'í', 'ó', 'ú'], ['', '', 'a', 'e', 'i', 'o', 'u'], $headerName));
            
            // Buscar el valor correspondiente
            $value = '';
            
            // Primero intentar con el mapeo directo
            if (isset($columnMapping[$headerName])) {
                $mappedKey = $columnMapping[$headerName];
                $value = $rowData[$mappedKey] ?? '';
            }
            
            // Si no se encontró, buscar por coincidencia normalizada
            if ($value === '') {
                foreach ($rowData as $key => $val) {
                    $normalizedKey = strtolower(str_replace(['.', ' ', 'á', 'é', 'í', 'ó', 'ú'], ['', '', 'a', 'e', 'i', 'o', 'u'], $key));
                    if ($normalizedKey === $normalizedHeader || 
                        strpos($normalizedKey, $normalizedHeader) !== false ||
                        strpos($normalizedHeader, $normalizedKey) !== false) {
                        $value = $val;
                        break;
                    }
                }
            }
            
            // Establecer el valor en la celda
            $cell = $worksheet->getCell($col . $currentRow);
            $cell->setValue($value);
        }
        $currentRow++;
    }
    
    // Aplicar bordes y alineación central a TODAS las celdas de la tabla
    $finalRow = $currentRow - 1;
    $colArray = range('A', $highestCol);
    
    for ($row = $dataStartRow; $row <= $finalRow; $row++) {
        foreach ($colArray as $col) {
            $cell = $worksheet->getCell($col . $row);
            
            // Aplicar bordes
            $cell->getStyle()->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => false
                ]
            ]);
        }
    }
    
    // Generar el archivo Excel
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    // Configurar headers para descarga
    $downloadFilename = $filename . '_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
    header('Cache-Control: max-age=0');
    
    // Enviar el archivo
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}

