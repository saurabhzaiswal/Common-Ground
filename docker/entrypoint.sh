#!/bin/sh
set -eu

PORT="${PORT:-10000}"
DB_DATABASE="${DB_DATABASE:-/var/www/html/storage/database/database.sqlite}"

case "$PORT" in
    ''|*[!0-9]*)
        echo "PORT must contain only digits." >&2
        exit 1
        ;;
esac

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required. Set it in the Render environment settings." >&2
    exit 1
fi

if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE"
fi

mkdir -p \
    bootstrap/cache \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown www-data:www-data \
    bootstrap/cache \
    storage \
    storage/app \
    storage/app/private \
    storage/app/public \
    storage/database \
    storage/framework \
    storage/framework/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

rm -rf public/storage
ln -s ../storage/app/public public/storage

sed -i -E "s/^Listen [0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

runuser -u www-data -- php artisan migrate --force --no-interaction
runuser -u www-data -- php artisan db:seed --force --no-interaction

exec apache2-foreground
