###############################
# 1) BUILD STAGE
###############################
FROM php:8.2-fpm AS build

# Install system deps & PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        gd \
        bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Fix git safe directory issue
RUN git config --global --add safe.directory /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Build Frontend Assets
RUN npm install && npm run production

# Laravel Optimizations
RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan optimize \
    && php artisan config:cache \
    && php artisan route:cache


###############################
# 2) RUNTIME STAGE
###############################
FROM alpine:3.18

# Install NGINX, PHP-FPM, Supervisor
RUN apk add --no-cache \
    nginx \
    php82 \
    php82-fpm \
    php82-opcache \
    php82-pdo \
    php82-pdo_mysql \
    php82-zip \
    php82-gd \
    php82-mbstring \
    php82-openssl \
    php82-session \
    php82-tokenizer \
    php82-xml \
    php82-ctype \
    supervisor

# Create directories
RUN mkdir -p /run/nginx
RUN mkdir -p /var/www/html

# Copy built app from stage 1
COPY --from=build /var/www/html /var/www/html

# Copy supervisor config
COPY deploy/supervisord.conf /etc/supervisord.conf

# Copy nginx config
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf

# Permissions
RUN chmod -R 755 /var/www/html
RUN chown -R nginx:nginx /var/www/html

# Expose port for Render to detect
EXPOSE 80

# Start everything through Supervisor
CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
