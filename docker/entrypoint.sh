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

# 2. Ensure storage, database, run, and bootstrap directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/cars \
         /var/www/html/database \
         /var/www/html/bootstrap/cache \
         /var/run

# 3. Create default .env if not exists
if [ ! -f /var/www/html/.env ]; then
    echo "[Padaidi] Initializing .env configuration..."
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# 4. Handle SQLite database file
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
if [ ! -f "$DB_PATH" ]; then
    echo "[Padaidi] Initializing SQLite database at ${DB_PATH}..."
    touch "$DB_PATH"
fi

# 5. Create storage symlink if not already created
if [ ! -L /var/www/html/public/storage ]; then
    echo "[Padaidi] Creating storage symlink..."
    php artisan storage:link || true
fi

# 6. Apply full read/write permissions for www-data on SQLite, sockets, and storage
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache /var/run
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache /var/run

# 7. Run database migrations & seeders ONLY if explicitly requested (DB is already pre-baked in Dockerfile)
AUTO_MIGRATE="${AUTO_MIGRATE:-false}"
AUTO_SEED="${AUTO_SEED:-false}"

if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "[Padaidi] Ensuring database migrations are up to date..."
    php artisan migrate --force --no-interaction || true
    if [ "$AUTO_SEED" = "true" ]; then
        echo "[Padaidi] Ensuring database seeds are up to date..."
        php artisan db:seed --force --no-interaction || true
    fi
fi

# 8. Re-apply permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache /var/run
chmod -R 777 /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache /var/run

# 9. Clear and warm up Laravel caches
echo "[Padaidi] Refreshing Laravel configuration, routes, and views..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[Padaidi] Application ready. Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
