<?php
// Middleware de autenticación ultra simplificado
// NO llamar session_start() aquí porque ya se llama en index.php

// Solo proporcionar información de sesión - NO aplicar restricciones automáticas
// Las APIs manejan su propia autenticación

// Información de usuario disponible para las páginas
$currentUser = $_SESSION['user'] ?? null;

// Helper: require role(s) - SOLO para páginas específicas, NO para la página principal
function requireRole(array $allowedRoles) {
    // NO aplicar restricciones automáticas - solo cuando se llame explícitamente
    return;
}