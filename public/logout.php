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

// Redirigir al login con JavaScript para limpiar historial
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cerrando sesión...</title>
</head>
<body>
    <script>
        // Limpiar historial del navegador y redirigir
        window.history.replaceState(null, null, "/login.php");
        window.location.href = "/login.php";
    </script>
    <p>Cerrando sesión...</p>
</body>
</html>';
exit;