# 🐦‍⬛ CrowDojo Academy - Sistema Completo

## 📋 Resumen del Sistema

Sistema completo de academia de hacking ético con:
- ✅ Modelo freemium (Módulo 1 gratis, Módulos 2-3 premium)
- ✅ Sistema de pagos con PosDigital y transferencia bancaria
- ✅ Panel de administración completo
- ✅ Gestión de comprobantes de pago
- ✅ Monitoreo de pagos en tiempo real

## 🎯 Funcionalidades Principales

### Para Usuarios:
- **Registro y Login** - Sistema de autenticación
- **Dashboard Principal** - Acceso a contenido gratuito y premium
- **Módulo Gratuito** - Contenido básico disponible sin pago
- **Sistema de Pago** - Múltiples métodos de pago
- **Monitoreo de Pago** - Seguimiento en tiempo real del estado

### Para Administradores:
- **Panel de Admin** - Dashboard completo de administración
- **Gestión de Pagos** - Aprobar/rechazar comprobantes
- **Vista de Comprobantes** - Visualización de archivos subidos
- **Gestión de Usuarios** - Administración completa de usuarios
- **Gestión de Videos** - CRUD completo de contenido

## 🔧 Archivos Principales

### Frontend:
- `index.php` - Página de inicio
- `dashboard.php` - Dashboard principal del usuario
- `course.php` - Contenido del curso
- `login.php` / `register.php` - Autenticación

### Sistema de Pagos:
- `payment-monitor.php` - Monitor de pagos PosDigital
- `payment.php` - Método alternativo (transferencia)
- `webhook-posdigital.php` - Webhook para notificaciones
- `check-payment-status.php` - API de verificación

### Panel de Admin:
- `admin/dashboard.php` - Dashboard de administración
- `admin/payment-management.php` - Gestión de pagos
- `admin/view-payment.php` - Vista detallada de comprobantes
- `admin/manage-users.php` - Gestión de usuarios
- `admin/manage-videos.php` - Gestión de contenido

### Configuración:
- `config/db.php` - Configuración de base de datos
- `config/admin.php` - Configuración de administradores

## 💳 Métodos de Pago

### 1. PosDigital (Principal)
- Integración completa con iframe
- Monitoreo en tiempo real
- Webhook automático
- Detección cross-tab

### 2. Transferencia Bancaria (Alternativo)
- **Banco:** Ueno Bank
- **Titular:** Fidel Acevedo Gonzalez
- **CI:** 4082736
- **Cuenta:** 6191108212

### 3. Personal Pay (Alternativo)
- **Número:** 0985 185 604
- **CI:** 4082736
- **Titular:** Fidel Acevedo Gonzalez

## 🛡️ Sistema de Administración

### Acceso de Admin:
- **Usuario ID = 1** es automáticamente administrador
- Botón visible en dashboard principal
- Acceso directo desde interfaz de usuario

### Funcionalidades de Admin:
- ✅ Ver todos los comprobantes de pago
- ✅ Aprobar/rechazar pagos con un clic
- ✅ Vista previa de imágenes y PDFs
- ✅ Estadísticas en tiempo real
- ✅ Gestión completa de usuarios

## 📊 Base de Datos

### Tabla `users`:
```sql
- id (PRIMARY KEY)
- username
- email
- password
- payment_status (pending/completed/failed)
- payment_reference (ruta del comprobante)
- payment_date
- created_at
```

### Tabla `videos`:
```sql
- id (PRIMARY KEY)
- title
- description
- video_url
- module (1, 2, 3)
- created_at
```

## 🚀 Estado del Sistema

### ✅ Completado:
- Sistema de autenticación
- Modelo freemium
- Integración de pagos múltiple
- Panel de administración
- Gestión de comprobantes
- Responsive design
- Sistema de webhooks

### 🎯 Listo para Producción:
- Todos los archivos de testing removidos
- Configuración de seguridad implementada
- Sistema de permisos funcionando
- Documentación completa

## 📁 Estructura de Archivos

```
/
├── index.php (Landing page)
├── dashboard.php (Dashboard principal)
├── course.php (Contenido del curso)
├── login.php / register.php (Autenticación)
├── payment-monitor.php (Monitor PosDigital)
├── payment.php (Pago alternativo)
├── webhook-posdigital.php (Webhook)
├── check-payment-status.php (API)
├── config/
│   ├── db.php (Base de datos)
│   └── admin.php (Configuración admin)
├── admin/
│   ├── dashboard.php (Panel admin)
│   ├── payment-management.php (Gestión pagos)
│   ├── view-payment.php (Vista comprobantes)
│   ├── manage-users.php (Gestión usuarios)
│   └── manage-videos.php (Gestión videos)
├── assets/
│   └── css/style.css (Estilos)
└── uploads/
    └── payments/ (Comprobantes subidos)
```

## 🔒 Seguridad

- ✅ Validación de archivos subidos
- ✅ Sanitización de inputs
- ✅ Control de acceso por roles
- ✅ Protección contra inyección SQL
- ✅ Validación de sesiones

## 📞 Soporte

Para cualquier problema o mejora:
1. Revisar logs de PHP
2. Verificar permisos de archivos
3. Comprobar configuración de base de datos
4. Usar archivos de debug si es necesario

---

**Sistema desarrollado para CrowDojo Academy**
*Domina el Arte del Hacking Ético* 🐦‍⬛