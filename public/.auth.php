<?php
// Guard simple para páginas PHP
session_start();

// Permitir pasar si ya está logueado
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

// Información de usuario disponible para las páginas
$currentUser = $_SESSION['user'];

// Helper: require role(s)
function requireRole(array $allowedRoles) {
    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
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


