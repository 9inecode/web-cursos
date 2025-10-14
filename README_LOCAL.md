# CrowDojo Academy - Configuración Local/Producción

## 🚀 Inicio Rápido

```bash
./start_dev.sh
```

## 💳 Sistema de Pagos

- **Monitor de Pagos**: `/payment-monitor.php`
- **Panel Admin**: `/admin/payment-management.php`
- **Webhook**: `/webhook-posdigital.php`
- **Verificación**: `/check-payment-status.php`

## 📋 Configuración

### Servicios Requeridos
- Apache2 (puerto 80)
- MariaDB (puerto 3306)
- PHP 8.4 con extensiones: mysql, mbstring, xml, curl

### Base de Datos
- **Host**: localhost
- **Base de datos**: hackademia_local
- **Usuario**: hackademia_user
- **Contraseña**: hackademia_pass

### Acceso Local
- **URL**: http://crowdojo.local
- **Directorio web**: /var/www/html/crowdojo/

## 👤 Usuarios de Prueba

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@crowdojo.local | password | Admin con acceso completo |
| test@crowdojo.local | password | Usuario básico |

## 🛠️ Comandos Útiles

### Sincronizar cambios
```bash
sudo rsync -av --exclude=hackademia.conf --exclude=start_dev.sh . /var/www/html/hackademia/
sudo chown -R www-data:www-data /var/www/html/hackademia/
```

### Ver logs de Apache
```bash
sudo tail -f /var/log/apache2/hackademia_error.log
sudo tail -f /var/log/apache2/hackademia_access.log
```

### Acceso a la base de datos
```bash
mysql -u hackademia_user -phackademia_pass hackademia_local
```

### Reiniciar servicios
```bash
sudo systemctl restart apache2
sudo systemctl restart mariadb
```

## 📁 Estructura del Proyecto

```
crowdojo-academy/
├── admin/              # Panel de administración
├── assets/             # CSS, JS, imágenes
├── config/             # Configuración de BD y mail
├── includes/           # Headers y footers
├── lib/                # Librerías (PHPMailer)
├── uploads/            # Archivos subidos
├── *.php              # Páginas principales
└── start_dev.sh       # Script de inicio
```

## 🔧 Configuración de Desarrollo

### Desactivar envío de emails
El archivo `config/config_local.php` tiene configurado:
```php
define('SEND_EMAILS', false);
```

### Base de datos local
El archivo `config/db.php` está configurado para usar la BD local.

## 🐛 Troubleshooting

### Error de conexión a BD
```bash
sudo systemctl status mariadb
mysql -u hackademia_user -phackademia_pass hackademia_local -e "SELECT 1;"
```

### Error 403/404 en Apache
```bash
sudo systemctl status apache2
sudo a2ensite hackademia.conf
sudo systemctl reload apache2
```

### Permisos de archivos
```bash
sudo chown -R www-data:www-data /var/www/html/hackademia/
sudo chmod -R 755 /var/www/html/hackademia/
```