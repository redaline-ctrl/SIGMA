FROM php:8.2-apache

# Instalar extensiones y cliente de PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mysqli

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos de Apache
RUN printf "<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

# Limpia sintaxis de MySQL (comillas, LOCK, unsigned, AUTO_INCREMENT) e importa a Postgres
CMD ["sh", "-c", "if [ -n \"$DB_HOST\" ]; then sed -i 's/`//g; /LOCK TABLES/d; /UNLOCK TABLES/d; s/unsigned//g; s/AUTO_INCREMENT//g' /var/www/html/sigma_db.sql && psql \"postgresql://$DB_USERNAME:$DB_PASSWORD@$DB_HOST:$DB_PORT/$DB_DATABASE\" -f /var/www/html/sigma_db.sql || true; fi && apache2-foreground"]

EXPOSE 80
