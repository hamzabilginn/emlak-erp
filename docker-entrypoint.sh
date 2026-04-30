#!/bin/sh
set -e
# Yüklenen ilan görselleri (PropertyController); volume mount öncesi klasör mevcut olsun
mkdir -p /var/www/html/public/uploads/properties
chown -R www-data:www-data /var/www/html/public/uploads 2>/dev/null || true
# Render (ve benzeri platformlar) dinlenecek portu PORT ile verir; yoksa 80.
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
exec "$@"
