FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instalar dependências
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
    mysql-client \
    linux-headers

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql zip intl opcache

# Instalar Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Configurar Xdebug
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html

# Expor porta do PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]