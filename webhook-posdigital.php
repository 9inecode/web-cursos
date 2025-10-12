<?php
require_once 'config/db.php';

// Log de webhooks para debugging
function logWebhook($data) {
    $log = date('Y-m-d H:i:s') . " - " . json_encode($data) . "\n";
    file_put_contents('logs/webhook.log', $log, FILE_APPEND | LOCK_EX);
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Obtener datos del webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log del webhook recibido
logWebhook([
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'body' => $input,
    'parsed' => $data,
    'current_hash' => '1157394' // Hash actual para referencia
]);

// Verificar que tenemos datos válidos
if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

try {
    // Aquí procesarías los datos según la estructura que envíe PosDigital
    // Ejemplo de estructura esperada:
    /*
    {
        "transaction_id": "12345",
        "status": "completed|failed|pending",
        "amount": 80000,
        "currency": "PYG",
        "user_reference": "user_email_or_id",
        "timestamp": "2023-10-11T12:00:00Z"
    }
    */
    
    $transaction_id = $data['transaction_id'] ?? null;
    $status = $data['status'] ?? null;
    $amount = $data['amount'] ?? null;
    $user_reference = $data['user_reference'] ?? null;
    
    if ($transaction_id && $status && $user_reference) {
        // Buscar usuario por email o ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR id = ?");
        $stmt->execute([$user_reference, $user_reference]);
        $user = $stmt->fetch();
        
        if ($user) {
            $user_id = $user['id'];
            
            // Actualizar estado del pago según el webhook
            switch (strtolower($status)) {
                case 'completed':
                case 'success':
                case 'approved':
                    $payment_status = 'completed';
                    break;
                case 'failed':
                case 'rejected':
                case 'cancelled':
                    $payment_status = 'failed';
                    break;
                default:
                    $payment_status = 'pending';
            }
            
            // Actualizar en la base de datos
            $stmt = $pdo->prepare("
                UPDATE users 
                SET payment_status = ?, 
                    payment_date = NOW(),
                    payment_reference = ?
                WHERE id = ?
            ");
            $stmt->execute([$payment_status, $transaction_id, $user_id]);
            
            // Log del procesamiento exitoso
            logWebhook([
                'action' => 'payment_updated',
                'user_id' => $user_id,
                'status' => $payment_status,
                'transaction_id' => $transaction_id
            ]);
            
            // Responder con éxito
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Payment updated']);
            
        } else {
            // Usuario no encontrado
            logWebhook(['error' => 'User not found', 'reference' => $user_reference]);
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
        
    } else {
        // Datos incompletos
        logWebhook(['error' => 'Missing required fields', 'data' => $data]);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }
    
} catch (Exception $e) {
    // Error en el procesamiento
    logWebhook(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
?>