FROM php:8.3-apache
RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite
RUN apt-get update && apt-get install -y libpng-dev libzip-dev libonig-dev && docker-php-ext-install pdo_mysql gd zip mbstring
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
EXPOSE 80
CMD ["apache2-foreground"]