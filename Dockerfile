FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev && docker-php-ext-install pdo_mysql zip && rm -rf /var/lib/apt/lists/*
COPY . /var/www/sigma/
WORKDIR /var/www/sigma
RUN chown -R www-data:www-data /var/www/sigma/storage 2>/dev/null || true
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]