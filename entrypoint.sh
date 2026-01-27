#!/bin/bash
set -e

# Helper function to ping the database
ping_db() {
  local db_host=$1
  local db_port=$2
  echo "Waiting for database at ${db_host}:${db_port}..."
  for i in $(seq 1 30); do
    if nc -z "$db_host" "$db_port" >/dev/null 2>&1; then
      echo "Database is up!"
      return 0
    fi
    sleep 1
  done
  echo "Database not available after 30 seconds. Exiting."
  exit 1
}

# Ensure .env file exists
ENV_FILE="/var/www/html/.env"
if [ ! -f "$ENV_FILE" ]; then
    echo "Creating .env file from .env.example..."
    cp /var/www/html/.env.example "$ENV_FILE"
fi

# Load environment variables for the script
# This is mainly to get DB_CONNECTION
# Note: Real PHP application will load it using phpdotenv
DB_CONNECTION=$(grep "^DB_CONNECTION=" "$ENV_FILE" | cut -d '=' -f 2 | tr -d '\r')
DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | cut -d '=' -f 2 | tr -d '\r')
DB_PORT=$(grep "^DB_PORT=" "$ENV_FILE" | cut -d '=' -f 2 | tr -d '\r')


# Ping database if it's MySQL or PostgreSQL
if [ "$DB_CONNECTION" = "mysql" ]; then
  ping_db "$DB_HOST" "$DB_PORT"
elif [ "$DB_CONNECTION" = "pgsql" ]; then
  ping_db "$DB_HOST" "$DB_PORT"
fi

# Ensure .data directory exists and has correct permissions for SQLite
mkdir -p /var/www/html/.data
chown www-data:www-data /var/www/html/.data
chmod -R 775 /var/www/html/.data

echo "Current user: $(whoami)"
echo "Permissions for /var/www/html/.data:"
ls -ld /var/www/html/.data

# Run migrations
php app/Database/init.php

# Set ServerName to suppress Apache warning
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername

# Start Apache
exec apache2-foreground