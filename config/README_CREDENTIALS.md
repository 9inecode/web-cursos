# 🔐 Configuración de Credenciales

## ⚠️ IMPORTANTE - Configuración Requerida

Para que el sistema de emails funcione, debes crear el archivo `email-credentials.php` en este directorio.

### 📝 Pasos de Configuración:

1. **Copia el archivo de ejemplo:**
   ```bash
   cp email-credentials.example.php email-credentials.php
   ```

2. **Edita el archivo con tus credenciales reales:**
   ```php
   return [
       'smtp_username' => 'tu-email@gmail.com',
       'smtp_password' => 'tu-contraseña-de-aplicacion-real',
       'from_email' => 'tu-email@gmail.com',
       'from_name' => 'CrowDojo Academy'
   ];
   ```

### 🔑 Generar Contraseña de Aplicación Gmail:

1. Ve a [Google Account Security](https://myaccount.google.com/security)
2. Activa **Verificación en 2 pasos**
3. Ve a **Contraseñas de aplicaciones**
4. Selecciona **Correo** y **Otro (nombre personalizado)**
5. Escribe "CrowDojo Academy"
6. **Copia la contraseña generada** (16 caracteres)
7. **Úsala en email-credentials.php**

### 🔒 Seguridad:

- ✅ El archivo `email-credentials.php` está en `.gitignore`
- ✅ NO se subirá a GitHub
- ✅ Solo existe en tu servidor local
- ✅ Protegido por `.htaccess`

### 🧪 Verificar Configuración:

Una vez configurado, puedes probar que funciona accediendo a cualquier página de pago. Deberías recibir notificaciones automáticas en tu email.

---

**⚠️ NUNCA subas este archivo a GitHub o repositorios públicos**