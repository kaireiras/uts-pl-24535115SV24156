FROM php:8.4-fpm-alpine


RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite-dev \
    mysql-client

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite gd zip

# Ambil Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Salin composer dahulu untuk caching layer (Syarat Soal 5a)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Salin seluruh kode aplikasi
COPY . .

# Generate autoloader produksi
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]