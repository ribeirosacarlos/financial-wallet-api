FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add --no-cache \
    git \
    zip \
    unzip \
    libzip-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    oniguruma-dev \
    mysql-client

RUN docker-php-ext-install pdo_mysql zip intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install --no-interaction --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]