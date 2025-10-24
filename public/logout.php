<?php
session_start();

// Versión simplificada sin SystemLogger para evitar errores 503
// TODO: Restaurar SystemLogger cuando se resuelvan los problemas de permisos

// Log simple del logout (sin SystemLogger)
if (isset($_SESSION['user'])) {
    error_log("LOGOUT: " . ($_SESSION['user']['username'] ?? 'unknown') . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: /login.php');
exit;
