FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

RUN a2enmod rewrite
# ABILITIAMO ANCHE IL MODULO REMOTEIP
RUN a2enmod remoteip

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --- FORZATURA HTTPS PER CLOUDFLARE/PROXY ---
# Diciamo ad Apache di considerare la connessione sicura se riceve l'header da Cloudflare
RUN echo 'SetEnvIf X-Forwarded-Proto "^https$" HTTPS=on' >> /etc/apache2/apache2.conf
# --------------------------------------------

# Listen 8080 + vhost 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/' /etc/apache2/sites-available/000-default.conf

# Single DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
# Aggiungi questo prima di CMD ["apache2-foreground"]
RUN chown -R www-data:www-data /var/www/html/public
RUN chmod -R 755 /var/www/html/public
CMD ["apache2-foreground"]
