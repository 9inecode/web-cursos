# 🐦‍⬛ CrowDojo Academy - Configuración Local

## 🚀 **Instalación Rápida**

### **1. Clonar el Repositorio**
```bash
git clone https://github.com/tu-usuario/crowdojo-academy.git
cd crowdojo-academy
```

### **2. Configurar Base de Datos**
```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE crowdojo_academy;
exit

# Importar estructura (si existe)
mysql -u root -p crowdojo_academy < database.sql
```

### **3. Configurar Credenciales**
```bash
# Copiar archivo de ejemplo
cp config/email-credentials.example.php config/email-credentials.php

# Editar con tus credenciales reales
nano config/email-credentials.php
```

### **4. Configurar Apache (Opcional)**
```bash
# Copiar configuración de ejemplo
sudo cp crowdojo.conf.example /etc/apache2/sites-available/crowdojo.conf

# Habilitar sitio
sudo a2ensite crowdojo.conf
sudo systemctl reload apache2

# Agregar al hosts
echo '127.0.0.1    crowdojo.local' | sudo tee -a /etc/hosts
```

### **5. Configurar Permisos**
```bash
# Dar permisos a Apache
sudo chown -R www-data:www-data uploads/
sudo chmod -R 755 uploads/
```

## 🌐 **URLs de Acceso**

- **Sitio**: http://localhost/crowdojo-academy/ o http://crowdojo.local
- **Admin**: http://crowdojo.local/admin/
- **Login**: admin@crowdojo.local / password

## 🔧 **Configuración de Email**

1. Obtén una **contraseña de aplicación** de Gmail
2. Edita `config/email-credentials.php`:
```php
return [
    'smtp_username' => 'tu-email@gmail.com',
    'smtp_password' => 'tu-contraseña-de-aplicacion',
    'from_email' => 'tu-email@gmail.com',
    'from_name' => 'CrowDojo Academy'
];
```

## 📋 **Requisitos**

- PHP 7.4+
- MySQL/MariaDB
- Apache con mod_rewrite
- Extensiones PHP: PDO, mysqli, curl, mbstring

## 🧪 **Testing**

```bash
# Verificar configuración
php -f check-admin.php

# Test de email (después de configurar credenciales)
php -f test-email.php
```

## 🔒 **Seguridad**

- ✅ Las credenciales están protegidas en `.gitignore`
- ✅ Los archivos de configuración local no se suben a GitHub
- ✅ Los uploads de pagos están protegidos

## 📞 **Soporte**

Si tienes problemas:
1. Verifica que Apache y MySQL estén corriendo
2. Revisa los logs de Apache: `/var/log/apache2/error.log`
3. Verifica permisos de archivos y directorios