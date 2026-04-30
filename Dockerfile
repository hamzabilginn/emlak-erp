FROM php:8.2-apache

# PostgreSQL istemci kitaplığı + PDO PostgreSQL uzantıları
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install -j"`nproc`" pdo pgsql pdo_pgsql curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# DocumentRoot: uygulama kökü /var/www/html, giriş noktası public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . /var/www/html/

# Windows CRLF ve UTF-8 BOM .htaccess'te Apache "Invalid command" hatası verir
RUN set -e; \
    for f in /var/www/html/public/.htaccess /var/www/html/.htaccess; do \
        if [ -f "$f" ]; then \
            sed -i 's/\r$//' "$f"; \
            first=`od -An -tx1 -N3 "$f" 2>/dev/null | tr -d ' \n' || true`; \
            if [ "$first" = "efbbbf" ]; then \
                tail -c +4 "$f" > "$f.nobom" && mv "$f.nobom" "$f"; \
            fi; \
        fi; \
    done

RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
