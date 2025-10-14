# 🔔 Sistema de Notificaciones por Email

## 📧 Configuración Inicial

### 1. Configurar tu Email
Edita el archivo `config/email-config.php` y cambia:

```php
'admin_email' => 'tu-email@gmail.com', // ⚠️ CAMBIAR POR TU EMAIL REAL
```

### 2. Tipos de Notificaciones

El sistema enviará emails automáticamente cuando:

- ✅ **Usuario accede a páginas de pago** (`payment-monitor.php`, `payment.php`)
- ✅ **Usuario inicia un pago** (abre ventana de PosDigital)
- ✅ **Usuario sube comprobante** (transferencia bancaria)
- ✅ **Pago se completa automáticamente** (webhook de PosDigital)

## 🎯 Eventos que Disparan Notificaciones

### 1. Acceso a Páginas de Pago
- **Cuándo:** Usuario visita `payment-monitor.php` o `payment.php`
- **Email:** "Usuario accedió a página de pago"
- **Información:** Página visitada, datos del usuario, IP

### 2. Intento de Pago
- **Cuándo:** Usuario abre ventana de PosDigital
- **Email:** "¡Nuevo intento de pago!"
- **Información:** Método de pago, datos del usuario

### 3. Comprobante Subido
- **Cuándo:** Usuario sube archivo en `payment.php`
- **Email:** "¡Comprobante de pago subido!"
- **Información:** Nombre del archivo, estado pendiente

### 4. Pago Completado
- **Cuándo:** Webhook confirma pago exitoso
- **Email:** "¡Pago completado automáticamente!"
- **Información:** Método de pago, acceso otorgado

## 📁 Archivos del Sistema

### Configuración:
- `config/email-config.php` - Configuración principal
- `config/notifications.php` - Funciones de notificación

### Integración:
- `payment-monitor.php` - Notifica acceso y intentos
- `payment.php` - Notifica acceso y uploads
- `webhook-posdigital.php` - Notifica pagos completados
- `notify-payment-action.php` - Endpoint AJAX

### Testing:
- `test-notifications.php` - Probar envío de emails

## 🔧 Personalización

### Habilitar/Deshabilitar Notificaciones
En `config/email-config.php`:

```php
'notifications' => [
    'payment_page_access' => true,    // Acceso a páginas
    'payment_attempt' => true,        // Intentos de pago
    'payment_proof_uploaded' => true, // Comprobantes subidos
    'payment_completed' => true,      // Pagos completados
]
```

### Cambiar Textos de Email
Edita las funciones en `config/notifications.php`:
- `notify_payment_page_access()`
- `notify_payment_attempt()`
- `notify_payment_proof_uploaded()`
- `notify_payment_completed()`

## 📊 Logs y Monitoreo

### Ver Logs
Los intentos de envío se registran en:
```
logs/notifications.log
```

### Formato del Log
```
2024-01-01 12:00:00 - Email notification: SUCCESS - Subject: [CrowDojo] Usuario accedió a página de pago
```

## 🧪 Probar el Sistema

1. **Configurar email** en `config/email-config.php`
2. **Acceder a** `test-notifications.php`
3. **Hacer clic** en los botones de prueba
4. **Verificar** tu email y los logs

## ⚠️ Requisitos del Servidor

### PHP mail() Function
- Debe estar habilitada en el servidor
- Configuración correcta de `sendmail_path`

### Para Gmail/SMTP (Opcional)
Si quieres usar SMTP en lugar de mail():
1. Habilitar `smtp_enabled => true`
2. Configurar credenciales SMTP
3. Instalar PHPMailer (no incluido)

## 🔒 Seguridad

- ✅ Validación de sesiones antes de enviar
- ✅ Sanitización de datos en emails
- ✅ Logs de todos los intentos
- ✅ Rate limiting implícito (por evento)

## 🚀 Funcionamiento en Producción

### Flujo Típico:
1. Usuario visita página de pago → **Email enviado**
2. Usuario inicia pago → **Email enviado**
3. Usuario sube comprobante → **Email enviado**
4. Admin aprueba pago → **Usuario notificado**
5. O webhook confirma pago → **Email enviado**

### Ejemplo de Email Recibido:
```
Asunto: [CrowDojo] ¡Comprobante de pago subido!

Un usuario ha subido un comprobante de pago.

Archivo: 67abc123def.jpg
El pago está pendiente de aprobación.

Usuario: juan_perez
Email: juan@email.com
ID: 15
IP: 192.168.1.100

[Ver Panel de Admin]
```

---

**¡Ahora recibirás notificaciones instantáneas de toda actividad de pago!** 🎉