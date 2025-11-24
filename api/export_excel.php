<?php
// Wrapper para export_excel.php en Vercel Serverless Functions
$publicDir = __DIR__ . '/../public';
chdir($publicDir);

$_SERVER['DOCUMENT_ROOT'] = $publicDir;
$_SERVER['SCRIPT_NAME'] = '/export_excel.php';
$_SERVER['PHP_SELF'] = '/export_excel.php';

$publicExportPath = $publicDir . '/export_excel.php';

if (file_exists($publicExportPath)) {
    require_once $publicExportPath;
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'export_excel.php no encontrado']);
}

