FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_prefork.load \
    && rm -f /etc/apache2/mods-enabled/mpm_prefork.conf \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
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

EXPOSE 80

CMD ["bash", "-c", "echo '=== MPM ENABLED ==='; ls -la /etc/apache2/mods-enabled/*mpm* 2>/dev/null || true; echo '=== LOADMODULE MPM ==='; grep -RniE '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2/mods-enabled 2>/dev/null || true; echo '=== APACHE CHECK ==='; apache2ctl -t; echo '=== ARRANCANDO APACHE ==='; exec apache2-foreground"]