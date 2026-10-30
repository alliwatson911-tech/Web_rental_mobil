# Multi-stage production Dockerfile for Padaidi Rent Car on Google Cloud Run
# Base image: PHP 8.3 FPM on lightweight Alpine Linux
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, Nginx, and Supervisord
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    bash

# Install PHP extensions using docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    bcmath \
    mbstring \
    opcache \
    gd \
    intl \
    zip \
    exif

# Install Composer from official Composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer definition files first to leverage Docker layer caching
COPY composer.json composer.lock ./

# Install Composer production dependencies (no dev dependencies, no scripts yet)
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copy the rest of the application code
COPY . .

# Run composer dump-autoload to execute package discovery and autoload optimization
RUN composer dump-autoload --optimize --no-dev

# Setup storage, public uploads, and bootstrap cache directory structure
RUN mkdir -p \
    storage/app/public/cars \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/padaidi.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Cloud Run expects the container to listen on the port defined by $PORT (default 8080)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

