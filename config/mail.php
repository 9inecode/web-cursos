<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'lib/PHPMailer/src/Exception.php';
require_once 'lib/PHPMailer/src/PHPMailer.php';
require_once 'lib/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body) {
    // Cargar credenciales desde archivo separado
    $credentials_file = __DIR__ . '/email-credentials.php';
    if (file_exists($credentials_file)) {
        $credentials = require $credentials_file;
    } else {
        // Fallback a credenciales por defecto si no existe el archivo
        $credentials = [
            'smtp_username' => 'fidelgnzf@gmail.com',
            'smtp_password' => 'TU_CONTRASEÑA_AQUI', // ⚠️ CAMBIAR
            'from_email' => 'fidelgnzf@gmail.com',
            'from_name' => 'CrowDojo Academy'
        ];
    }
    
    $mail = new PHPMailer(true);

    try {
        // Debug temporal para diagnosticar
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

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
        error_log("Intento de envío de correo a $to: " . ($result ? "Exitoso" : "Fallido"));
        return $result;
    } catch (Exception $e) {
        error_log("Error al enviar correo a $to: {$mail->ErrorInfo}");
        return false;
    }
}
