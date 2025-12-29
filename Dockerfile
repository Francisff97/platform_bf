# =========================
# Base image: PHP + Apache
# =========================
FROM php:8.3-apache

# =========================
# System dependencies
# =========================
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

# =========================
# Enable Apache rewrite
# =========================
RUN a2enmod rewrite

# =========================
# Set working directory
# =========================
WORKDIR /var/www/html

# =========================
# Copy project files
# =========================
COPY . .

# =========================
# Install Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# Install PHP dependencies
# =========================
RUN composer install --no-dev --optimize-autoloader

# =========================
# Install Node deps & build Vite
# =========================
RUN npm install && npm run build

# =========================
# Permissions
# =========================
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# =========================
# Apache config for Laravel
# =========================
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# =========================
# Expose port
# =========================
EXPOSE 8080

# =========================
# Start Apache
# =========================
CMD ["apache2-foreground"]
