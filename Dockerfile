# Production Dockerfile for Padaidi Rent Car on Render & Google Cloud Run
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
    bash \
    sqlite

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

# Install Composer production dependencies
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
    database \
    bootstrap/cache

# Setup default .env for build-time operations
RUN cp .env.example .env 2>/dev/null || touch .env

# Pre-bake SQLite database with all tables and vehicle seeders during build
ENV APP_ENV=production
ENV APP_KEY=base64:c5L2n3padaidiSecretKeyProductionRun123456=
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/var/www/html/database/database.sqlite
RUN touch /var/www/html/database/database.sqlite && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan storage:link || true

# Setup directory permissions
RUN chown -R www-data:www-data storage database bootstrap/cache public/storage && \
    chmod -R 777 storage database bootstrap/cache

# Setup Nginx runtime directories and clean default configs
RUN rm -rf /etc/nginx/conf.d/* /etc/nginx/http.d/* /etc/nginx/nginx.conf && \
    mkdir -p /var/lib/nginx/tmp /var/lib/nginx/html /var/log/nginx /var/run && \
    chown -R www-data:www-data /var/lib/nginx /var/log/nginx /var/run

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/padaidi.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Render and Cloud Run inject dynamic port (PORT variable)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
