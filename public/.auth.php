<?php
// Guard simple para páginas PHP
session_start();

// Solo aplicar autenticación para la página principal (no para APIs)
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isMainPage = ($currentUri === '/' || $currentUri === '/index.php' || $currentUri === '/index.html');

// Debug temporal
error_log("Auth Debug - URI: " . $currentUri);
error_log("Auth Debug - isMainPage: " . ($isMainPage ? 'true' : 'false'));
error_log("Auth Debug - hasSession: " . (isset($_SESSION['user']) ? 'true' : 'false'));
if (isset($_SESSION['user'])) {
    error_log("Auth Debug - user: " . json_encode($_SESSION['user']));
}

// Solo redirigir al login si es la página principal y no hay sesión
if ($isMainPage && !isset($_SESSION['user'])) {
    error_log("Auth Debug - Redirecting to login");
    header('Location: /login.php');
    exit;
}

// Información de usuario disponible para las páginas
$currentUser = $_SESSION['user'] ?? null;

// Helper: require role(s)
function requireRole(array $allowedRoles) {
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


