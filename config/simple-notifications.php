<?php
/**
 * Sistema de notificaciones simplificado usando mail() nativo
 * Como backup si PHPMailer no funciona
 */

/**
 * Enviar notificación simple por email
 */
function send_simple_notification($subject, $message, $user_data = []) {
    $to = 'fidelgnzf@gmail.com';
    $subject = "[CrowDojo] " . $subject;
    
    // Crear mensaje HTML simple
    $html_message = "
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial, sans-serif;'>
        <div style='background: #667eea; color: white; padding: 20px; text-align: center;'>
            <h1>🐦‍⬛ CrowDojo Academy</h1>
            <p>Notificación de Actividad de Pago</p>
        </div>
        
        <div style='padding: 20px;'>
            <h2>" . htmlspecialchars($subject) . "</h2>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>";
            
    if (!empty($user_data)) {
        $html_message .= "
            <div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                <h3>📋 Información del Usuario:</h3>
                <ul>";
        
        if (isset($user_data['id'])) $html_message .= "<li><strong>ID:</strong> " . $user_data['id'] . "</li>";
        if (isset($user_data['username'])) $html_message .= "<li><strong>Usuario:</strong> " . htmlspecialchars($user_data['username']) . "</li>";
        if (isset($user_data['email'])) $html_message .= "<li><strong>Email:</strong> " . htmlspecialchars($user_data['email']) . "</li>";
        if (isset($user_data['payment_status'])) $html_message .= "<li><strong>Estado:</strong> " . $user_data['payment_status'] . "</li>";
        if (isset($user_data['ip'])) $html_message .= "<li><strong>IP:</strong> " . $user_data['ip'] . "</li>";
        
        $html_message .= "
                </ul>
            </div>";
    }
    
    $html_message .= "
            <p><strong>⏰ Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
        </div>
        
        <div style='background: #333; color: white; padding: 10px; text-align: center; font-size: 12px;'>
            <p>CrowDojo Academy - Sistema de Notificaciones</p>
        </div>
    </body>
    </html>";
    
    // Headers para email HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: CrowDojo Academy <noreply@crowdojo.academy>" . "\r\n";
    $headers .= "Reply-To: noreply@crowdojo.academy" . "\r\n";
    
    // Intentar enviar el email
    $result = mail($to, $subject, $html_message, $headers);
    
    // Log del intento
    $log_message = date('Y-m-d H:i:s') . " - Simple notification: " . ($result ? "SUCCESS" : "FAILED") . " - Subject: $subject\n";
    error_log($log_message, 3, "logs/simple-notifications.log");
    
    return $result;
}

/**
 * Obtener información del usuario para notificaciones
 */
function get_simple_user_data($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, payment_status FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            return $user;
        }
    } catch (Exception $e) {
        error_log("Error getting user data: " . $e->getMessage());
    }
    
    return [];
}

/**
 * Notificar acceso a página de pago (versión simple)
 */
function notify_simple_payment_access($pdo, $user_id, $page) {
    $user_data = get_simple_user_data($pdo, $user_id);
    
    $subject = "Usuario accedió a página de pago";
    $message = "Un usuario ha accedido a una página relacionada con pagos.\n\n";
    $message .= "Página: $page\n";
    $message .= "Esto podría indicar interés en realizar un pago.";
    
    return send_simple_notification($subject, $message, $user_data);
}

/**
 * Notificar intento de pago (versión simple)
 */
function notify_simple_payment_attempt($pdo, $user_id, $method) {
    $user_data = get_simple_user_data($pdo, $user_id);
    
    $subject = "¡Nuevo intento de pago!";
    $message = "Un usuario ha iniciado un proceso de pago.\n\n";
    $message .= "Método de pago: $method\n";
    $message .= "Revisa el panel de administración para más detalles.";
    
    return send_simple_notification($subject, $message, $user_data);
}

/**
 * Notificar comprobante subido (versión simple)
 */
function notify_simple_proof_uploaded($pdo, $user_id, $filename) {
    $user_data = get_simple_user_data($pdo, $user_id);
    
    $subject = "¡Comprobante de pago subido!";
    $message = "Un usuario ha subido un comprobante de pago.\n\n";
    $message .= "Archivo: $filename\n";
    $message .= "El pago está pendiente de aprobación.";
    
    return send_simple_notification($subject, $message, $user_data);
}

/**
 * Notificar pago completado (versión simple)
 */
function notify_simple_payment_completed($pdo, $user_id, $method = 'PosDigital') {
    $user_data = get_simple_user_data($pdo, $user_id);
    
    $subject = "¡Pago completado automáticamente!";
    $message = "Se ha completado un pago de forma automática.\n\n";
    $message .= "Método: $method\n";
    $message .= "El usuario ahora tiene acceso completo al curso.";
    
    return send_simple_notification($subject, $message, $user_data);
}
?>