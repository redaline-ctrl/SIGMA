FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Enforce a single MPM to prevent AH00534 in Railway.
RUN set -eux; \
	a2dismod mpm_event || true; \
	a2dismod mpm_worker || true; \
	rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf; \
	a2enmod mpm_prefork rewrite headers; \
	sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN set -eux; \
	mpm_count="$(apache2ctl -M 2>/dev/null | grep -c '_mpm_module')"; \
	test "$mpm_count" -eq 1

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

RUN set -eux; \
	cat > /usr/local/bin/start-apache.sh <<'EOF'
#!/bin/sh
set -eu

# Railway safety: ensure only one MPM is active before Apache starts.
a2dismod mpm_event >/dev/null 2>&1 || true
a2dismod mpm_worker >/dev/null 2>&1 || true
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
  a2enmod mpm_prefork >/dev/null 2>&1 || true
fi

exec apache2-foreground
EOF
	chmod +x /usr/local/bin/start-apache.sh

EXPOSE 80
CMD ["/usr/local/bin/start-apache.sh"]