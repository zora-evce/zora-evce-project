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
COPY laravel-dashboard/php.ini /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /var/www/html

# Set user permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy application files
COPY . .

# Install dependencies using Composer
# RUN composer install

# Generate Laravel application key
# RUN php artisan key:generate

# Expose the PHP-FPM port
EXPOSE 9000

# Run the PHP-FPM process
CMD ["php-fpm"]
