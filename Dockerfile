FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache bash git nginx supervisor curl libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev oniguruma-dev libzip-dev icu-dev zlib-dev postgresql-dev nodejs npm

# Ajout des locales FR et EN
RUN apk add --no-cache icu-libs icu-data-full && \
    echo "fr_FR.UTF-8 UTF-8\nen_US.UTF-8 UTF-8" > /etc/locale.gen || true

ENV LANG=fr_FR.UTF-8
ENV LANGUAGE=fr_FR:en_US
ENV LC_ALL=fr_FR.UTF-8

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip intl opcache

# Install Composer & Symfony CLI
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Nginx & supervisord configs
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Set working directory and copy project
WORKDIR /var/www/html
COPY . .

# Install PHP & JS dependencies
RUN npm install
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Compile assets for production
RUN php bin/console asset-map:compile
RUN php bin/console cache:clear && php bin/console cache:warmup

# Entrypoint
COPY docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Prepare runtime permissions
RUN mkdir -p var/log && chown -R www-data:www-data var && chmod -R 755 /var/www/html
RUN mkdir -p /var/log/supervisor && chown -R www-data:www-data /var/log/supervisor && chmod -R 755 /var/log/supervisor

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
