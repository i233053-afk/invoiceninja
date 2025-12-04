# Use official PHP 8.2 FPM image
FROM php:8.2-fpm

# -----------------------------
# Install system dependencies & PHP extensions
# -----------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
       pdo \
       pdo_mysql \
       zip \
       gd \
       bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Install Composer
# -----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------
# Set working directory
# -----------------------------
WORKDIR /var/www/html

# -----------------------------
# Copy project files
# -----------------------------
COPY . .

# -----------------------------
# Fix git “dubious ownership” issue
# -----------------------------
RUN git config --global --add safe.directory /var/www/html

# -----------------------------
# Install PHP dependencies
# -----------------------------
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# -----------------------------
# Install Node dependencies and build frontend assets
# -----------------------------
RUN npm install && npm run production

# -----------------------------
# Setup Laravel environment
# -----------------------------
RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan optimize \
    && php artisan config:cache \
    && php artisan route:cache

# -----------------------------
# Expose port for PHP-FPM
# -----------------------------
EXPOSE 9000

# -----------------------------
# Start PHP-FPM
# -----------------------------
CMD ["php-fpm"]
