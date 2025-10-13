<?php
// Protección del directorio admin
session_start();

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    // Redirigir a login si no es admin
    header('Location: ../login.php');
    exit();
}

// Si es admin, redirigir al dashboard admin
header('Location: dashboard.php');
exit();
?>