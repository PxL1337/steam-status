FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache bash git nginx supervisor curl libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev oniguruma-dev libzip-dev icu-dev zlib-dev postgresql-dev

# PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip intl opcache

# Composer & Symfony CLI
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Nginx config & supervisord
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# ➕ Entrypoint intégré
COPY docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html
COPY . .

# Install deps
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN mkdir -p var/log && chown -R www-data:www-data var && chmod -R 755 /var/www/html

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
#CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
