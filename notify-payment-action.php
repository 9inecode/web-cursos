<?php
session_start();
require_once 'config/db.php';
require_once 'config/notifications.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Obtener datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'payment_window_opened':
            notify_payment_attempt($pdo, $_SESSION['user_id'], 'PosDigital');
            $response = ['success' => true, 'message' => 'Notificación enviada'];
            break;
            
        case 'payment_page_access':
            $page = $input['page'] ?? 'Página de pago';
            notify_payment_page_access($pdo, $_SESSION['user_id'], $page);
            $response = ['success' => true, 'message' => 'Acceso notificado'];
            break;
            
        case 'payment_proof_uploaded':
            $filename = $input['filename'] ?? 'archivo';
            notify_payment_proof_uploaded($pdo, $_SESSION['user_id'], $filename);
            $response = ['success' => true, 'message' => 'Upload notificado'];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Acción no válida'];
    }
} catch (Exception $e) {
    error_log("Error in notify-payment-action.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error interno'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>