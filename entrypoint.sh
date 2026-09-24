#!/bin/sh
set -e

mkdir -p /var/www/html/app/Services /var/www/html/app/Database
touch /var/www/html/app/Services/jobs.json

chown -R www-data:www-data /var/www/html/app/Services /var/www/html/app/Database
chmod 664 /var/www/html/app/Services/jobs.json
chmod -R 775 /var/www/html/app/Database

php do migrate

exec apache2-foreground