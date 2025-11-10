FROM php:8.3.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add custom php.ini
COPY php.ini /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /var/www/html

# Set user permissions (jalankan lebih awal)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 1. Salin HANYA file composer
COPY composer.json composer.lock ./

# 2. Instal dependensi (ini akan di-cache jika file lock tidak berubah)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 3. Salin sisa file aplikasi
COPY . .

# 4. Jalankan script composer (jika ada)
RUN composer run-script post-install-cmd --no-dev || true

# Generate Laravel application key
RUN php artisan key:generate

# Expose the PHP-FPM port
EXPOSE 9000

# Run the PHP-FPM process
CMD ["php-fpm"]