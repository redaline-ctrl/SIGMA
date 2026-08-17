FROM php:8.2-apache
COPY . /var/www/html/
RUN sed -i 's|/var/www/html|/var/www/html/público|g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite
EXPOSE 80