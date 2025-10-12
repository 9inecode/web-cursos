#!/bin/bash

echo "🐦‍⬛ Iniciando CrowDojo Academy en modo desarrollo..."

# Verificar servicios
echo "📋 Verificando servicios..."
sudo systemctl status apache2 --no-pager -l
sudo systemctl status mariadb --no-pager -l

# Iniciar servicios si no están corriendo
echo "🔧 Iniciando servicios necesarios..."
sudo systemctl start apache2
sudo systemctl start mariadb

# Sincronizar archivos
echo "📁 Sincronizando archivos..."
sudo rsync -av --exclude=hackademia.conf --exclude=start_dev.sh . /var/www/html/hackademia/
sudo chown -R www-data:www-data /var/www/html/hackademia/

# Verificar conexión
echo "🌐 Verificando conexión..."
if curl -s http://hackademia.local > /dev/null; then
    echo "✅ CrowDojo Academy está funcionando correctamente!"
    echo "🔗 Accede en: http://hackademia.local"
    echo "👤 Usuario de prueba: admin@hackademia.local / password"
else
    echo "❌ Error: No se puede conectar a la aplicación"
fi

echo "📊 Estado de la base de datos:"
mysql -u hackademia_user -phackademia_pass hackademia_local -e "SELECT 'Usuarios' as tabla, COUNT(*) as total FROM users UNION SELECT 'Videos', COUNT(*) FROM videos UNION SELECT 'Suscriptores', COUNT(*) FROM email_subscribers;"