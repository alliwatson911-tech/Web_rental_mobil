#!/bin/sh
set -e

echo "[Padaidi] Initializing Padaidi Rent Car for Google Cloud Run..."

# 1. Update Nginx listening port if PORT env variable is injected by Cloud Run
TARGET_PORT="${PORT:-8080}"
echo "[Padaidi] Configuring web server to listen on port ${TARGET_PORT}..."
sed -i "s/listen 8080/listen ${TARGET_PORT}/g" /etc/nginx/conf.d/default.conf
sed -i "s/listen \[::\]:8080/listen \[::\]:${TARGET_PORT}/g" /etc/nginx/conf.d/default.conf

# 2. Ensure storage and bootstrap directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/cars \
         /var/www/html/bootstrap/cache

# 3. Create storage symlink if not already created
if [ ! -L /var/www/html/public/storage ]; then
    echo "[Padaidi] Creating storage symlink..."
    php artisan storage:link || true
fi

# 4. Handle SQLite database if selected
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ ! -f "$DB_PATH" ]; then
        echo "[Padaidi] Initializing new SQLite database at ${DB_PATH}..."
        touch "$DB_PATH"
    fi
    chown www-data:www-data "$DB_PATH"
    chmod 664 "$DB_PATH"
fi

# 5. Fix permissions for www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Run database migration and seeder if requested
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "[Padaidi] Running database migrations..."
    php artisan migrate --force --no-interaction || true
    if [ "$AUTO_SEED" = "true" ]; then
        echo "[Padaidi] Running database seeders..."
        php artisan db:seed --force --no-interaction || true
    fi
fi

# 7. Warm up Laravel caches
echo "[Padaidi] Caching configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[Padaidi] Application ready. Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

