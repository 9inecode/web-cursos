<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

try {
    // Obtener datos del usuario
    $stmt = $pdo->prepare("SELECT payment_status, payment_date FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        exit();
    }
    
    // Verificar el estado actual
    $response = [
        'status' => $user['payment_status'],
        'payment_date' => $user['payment_date'],
        'message' => ''
    ];
    
    switch ($user['payment_status']) {
        case 'completed':
            $response['message'] = 'Pago confirmado y acceso activado';
            break;
        case 'pending':
            $response['message'] = 'Pago pendiente de confirmación';
            break;
        case 'failed':
            $response['message'] = 'Pago fallido o rechazado';
            break;
        default:
            $response['message'] = 'Sin información de pago';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor']);
}
?>