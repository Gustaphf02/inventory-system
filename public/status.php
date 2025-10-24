<?php
// Archivo de estado del sistema
header('Content-Type: application/json');

$status = [
    'php_version' => phpversion(),
    'extensions' => [
        'mongodb' => extension_loaded('mongodb'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring'),
        'openssl' => extension_loaded('openssl'),
        'curl' => extension_loaded('curl')
    ],
    'environment' => [
        'mongodb_uri_configured' => !empty($_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI')),
        'mongodb_uri_preview' => $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI') ? substr($_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI'), 0, 30) . '...' : 'Not configured'
    ],
    'classes' => [
        'mongodb_client' => class_exists('MongoDB\Client'),
        'mongodb_objectid' => class_exists('MongoDB\BSON\ObjectId')
    ]
];

echo json_encode($status, JSON_PRETTY_PRINT);
?>
