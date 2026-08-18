FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/sigma/

RUN sed -ri 's!/var/www/html!/var/www/sigma/public!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/sigma/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' \
    > /etc/apache2/conf-available/sigma.conf \
    && a2enconf sigma \
    && chown -R www-data:www-data /var/www/sigma/storage

WORKDIR /var/www/sigma

EXPOSE 80

CMD ["apache2-foreground"]