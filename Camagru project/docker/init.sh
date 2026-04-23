#!/bin/sh

mkdir -p /var/www/html/public/uploads

chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 755 /var/www/html/public/uploads

echo "Permessi sistemati"

exec apache2-foreground