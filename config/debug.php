<?php
// Configuración de debug para el sistema
// Cambiar a false en producción

return [
    'email_debug' => false,        // Debug de PHPMailer
    'notification_logs' => true,   // Logs de notificaciones (solo éxitos)
    'payment_logs' => false,       // Logs detallados de pagos
    'webhook_logs' => true,        // Logs de webhooks
    'error_logs' => true,          // Logs de errores críticos
];
?>