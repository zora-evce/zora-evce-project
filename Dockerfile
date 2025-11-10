FROM php:8.3.0-fpm

# System deps
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    git unzip curl \
 && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip

# Opcache (recommended for prod)
RUN docker-php-ext-install opcache

# ---- phpredis (the missing piece) ----
RUN pecl install redis \
 && docker-php-ext-enable redis
# --------------------------------------

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Workdir
WORKDIR /var/www/html

# Copy only composer files first (layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy app
COPY . .

# Permissions (only if you really need these here)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# PHP config (optional)
COPY php.ini /usr/local/etc/php/php.ini

# ---- Avoid baking secrets into image ----
# Don't run artisan commands that require .env at build time.
# If you REALLY need APP_KEY in the image, copy .env.example -> .env first:
# RUN cp .env.example .env && php artisan key:generate && rm .env
# Better: generate APP_KEY at runtime or via CI/CD / secrets.

EXPOSE 9000
CMD ["php-fpm"]
