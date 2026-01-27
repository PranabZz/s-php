FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
        libpq-dev \
        libsqlite3-dev \
        zip unzip \
        netcat-traditional \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite \
    && docker-php-ext-enable pdo pdo_mysql pdo_pgsql pdo_sqlite

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY ./composer.json ./composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN chmod +x ./entrypoint.sh

EXPOSE 80

