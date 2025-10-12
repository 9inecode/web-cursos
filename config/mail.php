<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'lib/PHPMailer/src/Exception.php';
require_once 'lib/PHPMailer/src/PHPMailer.php';
require_once 'lib/PHPMailer/src/SMTP.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Debug detallado
        $mail->SMTPDebug = 3;  // Nivel máximo de debug
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fidelgnzf@gmail.com'; // Tu correo
        $mail->Password = 'xhrx xdma zqff fhbo'; // Tu contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('fidelgnzf@gmail.com', 'Hackademia');
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
