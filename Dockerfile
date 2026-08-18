FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Dejar SOLO prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2dismod mpm_prefork 2>/dev/null || true \
    && rm -f /etc/apache2/mods-enabled/mpm_* \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

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

RUN echo "=== MPM FINAL EN BUILD ===" \
    && ls -la /etc/apache2/mods-enabled/*mpm* 2>/dev/null || true \
    && echo "=== LOADMODULE FINAL EN BUILD ===" \
    && grep -RniE '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2/mods-enabled 2>/dev/null || true \
    && echo "=== APACHE TEST ===" \
    && apache2ctl -t

CMD ["apache2-foreground"]