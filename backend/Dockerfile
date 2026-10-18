FROM dunglas/frankenphp:1-php8.4-alpine

# Cukup pasang pdo_mysql dan zip (hanya butuh hitungan detik, tanpa kompilasi C++ berat)
RUN install-php-extensions \
    pdo_mysql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Cache layer dependensi Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

ENV SERVER_NAME=":80"
ENV CADDY_GLOBAL_OPTIONS="auto_https off"

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]