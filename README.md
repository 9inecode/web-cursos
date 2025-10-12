# 🐦‍⬛ CrowDojo Academy

Sistema completo de academia de hacking ético con modelo freemium y notificaciones automáticas por email.

## ✨ Características Principales

### 🎯 **Modelo Freemium**
- **Módulo 1**: Contenido gratuito para todos los usuarios
- **Módulos 2-3**: Contenido premium que requiere pago
- **Acceso de por vida**: Una sola compra, acceso permanente

### 💳 **Sistema de Pagos Múltiple**
- **PosDigital**: Integración completa con monitoreo en tiempo real
- **Transferencia Bancaria**: Ueno Bank con upload de comprobantes
- **Personal Pay**: Pago móvil alternativo

### 🔔 **Notificaciones Automáticas**
- **Email instantáneo** al admin cuando usuarios:
  - Acceden a páginas de pago
  - Inician procesos de pago
  - Suben comprobantes de transferencia
  - Completan pagos automáticamente

### 🛡️ **Panel de Administración**
- **Gestión de pagos**: Aprobar/rechazar comprobantes
- **Vista de comprobantes**: Visualización de imágenes y PDFs
- **Gestión de usuarios**: Control completo de accesos
- **Estadísticas en tiempo real**: Dashboard completo

## 🚀 Instalación

### Requisitos
- PHP 7.4+
- MySQL/MariaDB
- Apache/Nginx
- PHPMailer
- Extensiones: OpenSSL, cURL

### Configuración Inicial

1. **Clonar repositorio**
```bash
git clone https://github.com/tu-usuario/crowdojo-academy.git
cd crowdojo-academy
```

2. **Configurar base de datos**
```bash
# Importar estructura de base de datos
mysql -u usuario -p nombre_bd < database/structure.sql
```

3. **Configurar credenciales de email**
```bash
# Copiar archivo de ejemplo
cp config/email-credentials.example.php config/email-credentials.php

# Editar con tus credenciales reales
nano config/email-credentials.php
```

4. **Configurar base de datos**
```bash
# Copiar y editar configuración
cp config/db.example.php config/db.php
nano config/db.php
```

## 📧 Configuración de Email

### Gmail SMTP
1. Activa la verificación en 2 pasos en tu cuenta Gmail
2. Genera una contraseña de aplicación
3. Configura en `config/email-credentials.php`:

```php
return [
    'smtp_username' => 'tu-email@gmail.com',
    'smtp_password' => 'tu-contraseña-de-aplicacion',
    'from_email' => 'tu-email@gmail.com',
    'from_name' => 'CrowDojo Academy'
];
```

## 💰 Configuración de Pagos

### PosDigital
- Configura tu merchant ID en `payment-monitor.php`
- Webhook URL: `tu-dominio.com/webhook-posdigital.php`

### Transferencia Bancaria
- Actualiza datos bancarios en `payment.php`
- Configura directorio de uploads: `uploads/payments/`

## 🎨 Personalización

### Tema y Branding
- Colores principales: `#667eea` (azul) y `#764ba2` (púrpura)
- Logo y nombre: CrowDojo Academy 🐦‍⬛
- Tema: Hacking ético / Dojo de guerreros cibernéticos

### Contenido del Curso
- Edita módulos en `course.php`
- Gestiona videos desde el panel admin
- Personaliza mensajes y textos

## 📁 Estructura del Proyecto

```
/
├── index.php              # Página de inicio
├── dashboard.php           # Dashboard principal
├── course.php             # Contenido del curso
├── payment-monitor.php    # Monitor de pago PosDigital
├── payment.php           # Pago por transferencia
├── webhook-posdigital.php # Webhook automático
├── admin/                # Panel de administración
│   ├── dashboard.php     # Dashboard admin
│   ├── payment-management.php # Gestión de pagos
│   └── view-payment.php  # Vista de comprobantes
├── config/               # Configuración
│   ├── db.php           # Base de datos
│   ├── mail.php         # Sistema de email
│   └── notifications.php # Notificaciones
├── assets/css/          # Estilos
├── uploads/payments/    # Comprobantes subidos
└── logs/               # Logs del sistema
```

## 🔒 Seguridad

### Archivos Sensibles
Los siguientes archivos contienen credenciales y NO deben subirse a GitHub:
- `config/email-credentials.php`
- `config/db_local.php`
- `uploads/payments/*`
- `logs/*.log`

### Validaciones Implementadas
- ✅ Validación de archivos subidos
- ✅ Sanitización de inputs
- ✅ Control de acceso por roles
- ✅ Protección CSRF
- ✅ Validación de sesiones

## 📊 Funcionalidades del Sistema

### Para Usuarios
- [x] Registro y login
- [x] Acceso a módulo gratuito
- [x] Proceso de pago múltiple
- [x] Upload de comprobantes
- [x] Dashboard personalizado

### Para Administradores
- [x] Panel de administración completo
- [x] Gestión de pagos y comprobantes
- [x] Notificaciones por email
- [x] Estadísticas en tiempo real
- [x] Gestión de usuarios y contenido

## 🛠️ Desarrollo

### Comandos Útiles
```bash
# Limpiar logs
rm logs/*.log

# Verificar permisos
chmod 755 uploads/payments/

# Test de email
php test-email.php
```

### Contribuir
1. Fork del repositorio
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📞 Soporte

Para problemas o preguntas:
1. Revisar logs en `logs/`
2. Verificar configuración de email
3. Comprobar permisos de archivos
4. Revisar documentación en `/docs`

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

---

**Desarrollado con ❤️ para CrowDojo Academy**  
*Domina el Arte del Hacking Ético* 🐦‍⬛