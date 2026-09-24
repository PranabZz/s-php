FROM php:8.3-apache

# Install dependencies for PostgreSQL and SQLite
RUN apt-get update && apt-get install -y \
  libpq-dev \
  libsqlite3-dev \
  zip \
  unzip \
  && docker-php-ext-install pdo_mysql pdo_pgsql \
  && pecl install scrypt \
  && docker-php-ext-enable scrypt \
  && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY ./composer.json ./composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize
RUN chmod +x ./entrypoint.sh
