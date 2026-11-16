#!/bin/sh
set -e

cd /var/www/html

php artisan storage:link >/dev/null 2>&1 || true

php artisan migrate --force

if [ "$RUN_SEED" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan view:cache

exec apache2-foreground
