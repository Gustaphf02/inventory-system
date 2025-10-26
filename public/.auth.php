<?php
// Middleware de autenticación simplificado
// NO iniciar sesión aquí - se maneja en index.php

// Solo proporcionar información de sesión - NO aplicar restricciones automáticas
// Las APIs manejan su propia autenticación

// Información de usuario disponible para las páginas
$currentUser = $_SESSION['user'] ?? null;

// Helper: require role(s)
function requireRole(array $allowedRoles) {
    if (!isset($_SESSION['user'])) {
        // Usuario NO autenticado - redirigir al login
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
    
    // Usuario autenticado - verificar rol
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        // Usuario autenticado pero SIN permisos - mostrar error de acceso denegado
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
            strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso denegado', 'message' => 'No tienes permisos para esta acción']);
            exit;
        }
        
        // Mostrar página de acceso denegado (NO redirigir al login)
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><meta charset="utf-8">'
           . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>'
           . '<div class="container py-5"><div class="alert alert-danger">'
           . '<h4>Acceso Denegado</h4>'
           . '<p>No tienes permisos para acceder a esta página.</p>'
           . '<p>Tu rol es: <strong>' . htmlspecialchars($role) . '</strong></p>'
           . '<p>Roles permitidos: <strong>' . htmlspecialchars(implode(', ', $allowedRoles)) . '</strong></p>'
           . '</div>'
           . '<a class="btn btn-primary" href="/">Volver al Sistema</a> '
           . '<a class="btn btn-outline-secondary ms-2" href="/logout.php">Cerrar Sesión</a>'
           . '</div></body></html>';
        exit;
    }
}


