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


