<?php
// Configuración de base de datos - Archivo de ejemplo
// Copia este archivo como db.php y configura tus datos reales

$host = 'localhost';
$dbname = 'crowdojo_academy';
$username = 'tu_usuario_db';
$password = 'tu_contraseña_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>