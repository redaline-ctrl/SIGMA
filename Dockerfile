FROM php:8.3-cli

RUN apt-get update && apt-get install -y libpng-dev libzip-dev unzip git \
    && docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 775 storage bootstrap/cache

CMD php artisan serve --host=0.0.0.0 --port=$PORT