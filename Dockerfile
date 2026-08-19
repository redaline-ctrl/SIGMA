FROM php:8.3-apache

RUN apt-get update && apt-get install -y libpng-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_mysql gd zip mbstring

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]