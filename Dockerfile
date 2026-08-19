FROM php:8.3-apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN a2dismod mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; \
    a2enmod mpm_prefork rewrite headers; \
    sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf; \
    a2enconf servername

RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf && \
    a2enconf override    

RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libzip-dev libonig-dev libxml2-dev unzip \
    && docker-php-ext-install pdo_mysql gd zip mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/storage || true

RUN printf '#!/bin/sh\nset -eu\na2dismod mpm_event >/dev/null 2>&1 || true\na2dismod mpm_worker >/dev/null 2>&1 || true\nrm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*\na2enmod mpm_prefork >/dev/null 2>&1 || true\nexec apache2-foreground\n' > /usr/local/bin/start-apache.sh && chmod +x /usr/local/bin/start-apache.sh

EXPOSE 80
CMD ["/usr/local/bin/start-apache.sh"]