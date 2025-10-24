<?php
// Middleware de autenticación completamente limpio
// NO llamar session_start() aquí porque ya se llama en index.php

// Solo proporcionar información de sesión - NO aplicar restricciones automáticas
// Las APIs manejan su propia autenticación

// Información de usuario disponible para las páginas
$currentUser = $_SESSION['user'] ?? null;

// Helper: require role(s) - SOLO para páginas específicas, NO para la página principal
function requireRole(array $allowedRoles) {
    // Solo aplicar restricciones si NO es la página principal
    if (strpos($_SERVER['REQUEST_URI'], '/') === 0 && $_SERVER['REQUEST_URI'] === '/') {
        return; // No aplicar restricciones a la página principal
    }
    
    if (!isset($_SESSION['user'])) {
        // Si es una llamada AJAX/API, devolver JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
            strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado', 'message' => 'Debes iniciar sesión']);
            exit;
        }
        header('Location: /login.php');
        exit;
    }
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        // Si es una llamada AJAX/API, devolver JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
            strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso denegado', 'message' => 'No tienes permisos para esta acción']);
            exit;
        }
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8">'
           . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>'
           . '<div class="container py-5"><div class="alert alert-danger">'
           . 'Acceso denegado. No tienes permisos para ver esta página.</div>'
           . '<a class="btn btn-primary" href="/">Volver al Sistema</a> '
           . '<a class="btn btn-outline-secondary ms-2" href="/logout.php">Cerrar Sesión</a>'
           . '</div></body></html>';
        exit;
    }
}
