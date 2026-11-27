# Memakai image lokal: php:8.5-fpm + binary composer dari composer:latest
FROM composer:latest AS composer

FROM php:8.5-fpm

ARG UID=1000
ARG GID=1000

# Ekstensi pdo_mysql dikompilasi dari source bawaan image (tanpa apt)
RUN docker-php-ext-install pdo_mysql \
    && groupmod -o -g ${GID} www-data \
    && usermod -o -u ${UID} -g ${GID} www-data

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

WORKDIR /var/www/html
USER www-data

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
