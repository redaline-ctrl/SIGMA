FROM php:8.2-apache

# Instalar extensiones PDO y cliente de PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mysqli

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Apuntar DocumentRoot a public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos
RUN printf "<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

# Script de arranque: importa la base de datos si existe DB_HOST y luego inicia Apache
CMD ["sh", "-c", "if [ -n \"$DB_HOST\" ]; then PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE -f /var/www/html/sigma_db.sql || true; fi && apache2-foreground"]

EXPOSE 80
