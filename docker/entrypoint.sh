#!/bin/sh
set -e

echo "[Padaidi] Initializing Padaidi Rent Car Container..."

# 1. Update Nginx listening port if PORT env variable is injected by Cloud Provider (Render / Cloud Run)
TARGET_PORT="${PORT:-8080}"
echo "[Padaidi] Configuring web server to listen on port ${TARGET_PORT}..."
sed -i "s/listen 8080/listen ${TARGET_PORT}/g" /etc/nginx/nginx.conf
sed -i "s/listen \[::\]:8080/listen \[::\]:${TARGET_PORT}/g" /etc/nginx/nginx.conf

# Test Nginx configuration before proceeding
echo "[Padaidi] Validating Nginx configuration..."
nginx -t

# 2. Ensure storage, database, and bootstrap directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/cars \
         /var/www/html/database \
         /var/www/html/bootstrap/cache

# 3. Handle SQLite database file
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
if [ ! -f "$DB_PATH" ]; then
    echo "[Padaidi] Initializing SQLite database at ${DB_PATH}..."
    touch "$DB_PATH"
fi

# 4. Create storage symlink if not already created
if [ ! -L /var/www/html/public/storage ]; then
    echo "[Padaidi] Creating storage symlink..."
    php artisan storage:link || true
fi

# 5. Fix permissions for www-data before migration
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache

# 6. Run database migrations & seeders if needed
AUTO_MIGRATE="${AUTO_MIGRATE:-true}"
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "[Padaidi] Ensuring database migrations are up to date..."
    php artisan migrate --force --no-interaction || true
    if [ "$AUTO_SEED" = "true" ]; then
        echo "[Padaidi] Ensuring database seeds are up to date..."
        php artisan db:seed --force --no-interaction || true
    fi
fi

# 7. Re-apply strict permissions for SQLite writeability by www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache

# 8. Warm up Laravel caches
echo "[Padaidi] Caching configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[Padaidi] Application ready. Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
