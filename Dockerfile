FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Enforce a single MPM to prevent AH00534 in Railway.
RUN set -eux; \
	a2dismod mpm_event || true; \
	a2dismod mpm_worker || true; \
	rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf; \
	a2enmod mpm_prefork rewrite headers; \
	sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libpng-dev libzip-dev libonig-dev libxml2-dev unzip \
	&& docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip mbstring \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage \
	&& chown -R www-data:www-data /var/www/html \
	&& find /var/www/html -type d -exec chmod 755 {} \; \
	&& find /var/www/html -type f -exec chmod 644 {} \; \
	&& chmod -R 775 /var/www/html/storage \
	&& echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
	&& a2enconf servername

EXPOSE 80
CMD ["apache2-foreground"]