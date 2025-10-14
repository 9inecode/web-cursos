<?php
// Protección del directorio admin
session_start();

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirigir a login si no es admin
    header('Location: ../login.php');
    exit();
}

// Si es admin, redirigir al dashboard admin
header('Location: dashboard.php');
exit();
?>