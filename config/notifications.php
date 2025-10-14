<?php
// Función específica para notificaciones de pago
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar PHPMailer con rutas absolutas
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $base_dir = dirname(__DIR__); // Directorio raíz del proyecto
    require_once $base_dir . '/lib/PHPMailer/src/Exception.php';
    require_once $base_dir . '/lib/PHPMailer/src/PHPMailer.php';
    require_once $base_dir . '/lib/PHPMailer/src/SMTP.php';
}

/**
 * Función específica para enviar notificaciones de pago
 */
function sendPaymentNotification($to, $subject, $body) {
    // Cargar credenciales desde archivo separado
    $credentials_file = __DIR__ . '/email-credentials.php';
    if (file_exists($credentials_file)) {
        $credentials = require $credentials_file;
    } else {
        error_log("Error: No se encontró archivo de credenciales email-credentials.php");
        return false;
    }
    
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $credentials['smtp_username'];
        $mail->Password = $credentials['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom($credentials['from_email'], $credentials['from_name']);
        $mail->addAddress($to);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = 'UTF-8';

        // Intentar enviar
        $result = $mail->send();
        return $result;
    } catch (Exception $e) {
        // Log solo errores críticos
        error_log("Error crítico en notificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Enviar notificación por email al administrador
 * @param string $subject Asunto del email
 * @param string $message Mensaje del email
 * @param array $user_data Datos del usuario (opcional)
 * @return bool
 */
function send_admin_notification($subject, $message, $user_data = []) {
    $to = 'fidelgnzf@gmail.com';
    $subject = "[CrowDojo] " . $subject;
    
    // Crear el mensaje HTML
    $html_message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .user-info { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; font-size: 12px; }
            .timestamp { color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🐦‍⬛ CrowDojo Academy</h1>
            <p>Notificación de Actividad de Pago</p>
        </div>
        
        <div class='content'>
            <h2>" . htmlspecialchars($subject) . "</h2>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
            
            " . (!empty($user_data) ? "
            <div class='user-info'>
                <h3>📋 Información del Usuario:</h3>
                <ul>
                    " . (isset($user_data['id']) ? "<li><strong>ID:</strong> " . $user_data['id'] . "</li>" : "") . "
                    " . (isset($user_data['username']) ? "<li><strong>Usuario:</strong> " . htmlspecialchars($user_data['username']) . "</li>" : "") . "
                    " . (isset($user_data['email']) ? "<li><strong>Email:</strong> " . htmlspecialchars($user_data['email']) . "</li>" : "") . "
                    " . (isset($user_data['payment_status']) ? "<li><strong>Estado de Pago:</strong> " . $user_data['payment_status'] . "</li>" : "") . "
                    " . (isset($user_data['ip']) ? "<li><strong>IP:</strong> " . $user_data['ip'] . "</li>" : "") . "
                </ul>
            </div>
            " : "") . "
            
            <div class='timestamp'>
                <p>⏰ Fecha y Hora: " . date('d/m/Y H:i:s') . "</p>
            </div>
            
            <p><a href='http://" . $_SERVER['HTTP_HOST'] . "/admin/payment-management.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Panel de Admin</a></p>
        </div>
        
        <div class='footer'>
            <p>CrowDojo Academy - Sistema de Notificaciones Automáticas</p>
        </div>
    </body>
    </html>
    ";
    
    // Usar función específica para notificaciones de pago
    $result = sendPaymentNotification($to, $subject, $html_message);
    
    // Log simplificado solo para éxitos
    if ($result) {
        $log_message = date('Y-m-d H:i:s') . " - Notificación enviada: $subject\n";
        error_log($log_message, 3, "logs/notifications.log");
    }
    
    return $result;
}

/**
 * Obtener información del usuario actual
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function get_user_notification_data($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, payment_status FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $user['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            return $user;
        }
    } catch (Exception $e) {
        error_log("Error getting user data for notification: " . $e->getMessage());
    }
    
    return [];
}

/**
 * Notificar acceso a página de pago
 */
function notify_payment_page_access($pdo, $user_id, $page) {
    
    $user_data = get_user_notification_data($pdo, $user_id);
    
    $subject = "Usuario accedió a página de pago";
    $message = "Un usuario ha accedido a una página relacionada con pagos.\n\n";
    $message .= "Página: $page\n";
    $message .= "Esto podría indicar interés en realizar un pago.";
    
    send_admin_notification($subject, $message, $user_data);
}

/**
 * Notificar intento de pago
 */
function notify_payment_attempt($pdo, $user_id, $method) {
    
    $user_data = get_user_notification_data($pdo, $user_id);
    
    $subject = "¡Nuevo intento de pago!";
    $message = "Un usuario ha iniciado un proceso de pago.\n\n";
    $message .= "Método de pago: $method\n";
    $message .= "Revisa el panel de administración para más detalles.";
    
    send_admin_notification($subject, $message, $user_data);
}

/**
 * Notificar comprobante subido
 */
function notify_payment_proof_uploaded($pdo, $user_id, $filename) {
    
    $user_data = get_user_notification_data($pdo, $user_id);
    
    $subject = "¡Comprobante de pago subido!";
    $message = "Un usuario ha subido un comprobante de pago.\n\n";
    $message .= "Archivo: $filename\n";
    $message .= "El pago está pendiente de aprobación.";
    
    send_admin_notification($subject, $message, $user_data);
}

/**
 * Notificar pago completado (webhook)
 */
function notify_payment_completed($pdo, $user_id, $method = 'PosDigital') {
    
    $user_data = get_user_notification_data($pdo, $user_id);
    
    $subject = "¡Pago completado automáticamente!";
    $message = "Se ha completado un pago de forma automática.\n\n";
    $message .= "Método: $method\n";
    $message .= "El usuario ahora tiene acceso completo al curso.";
    
    send_admin_notification($subject, $message, $user_data);
}
?>