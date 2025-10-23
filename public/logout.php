<?php
session_start();

// Incluir el sistema de logging
require_once __DIR__ . '/includes/SystemLogger.php';

// Log del logout antes de destruir la sesión
if (isset($_SESSION['user'])) {
    SystemLogger::logAuth('LOGOUT', $_SESSION['user']['username'], true, "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8">
<script>
  localStorage.removeItem('token');
  window.location.href = '/login.php';
</script>
</head><body></body></html>
