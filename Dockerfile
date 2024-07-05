FROM php:8.0-apache
RUN apt-get update && apt-get install -y libonig-dev && docker-php-ext-install pdo_mysql && pecl install xdebug && docker-php-ext-enable xdebug