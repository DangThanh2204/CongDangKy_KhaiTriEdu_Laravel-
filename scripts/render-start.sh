#!/usr/bin/env bash
set -e

cd /var/www/html

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="${APP_URL:-$RENDER_EXTERNAL_URL}"
  export VNPAY_RETURN_URL="${VNPAY_RETURN_URL:-$RENDER_EXTERNAL_URL/payments/vnpay/return}"
  export VNPAY_IPN_URL="${VNPAY_IPN_URL:-$RENDER_EXTERNAL_URL/payments/vnpay/ipn}"
fi

if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "APP_KEY was missing, generated a temporary key for this container."
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  export DB_DATABASE="${DB_DATABASE:-/var/www/html/storage/app/render.sqlite}"
  mkdir -p "$(dirname "$DB_DATABASE")"
  touch "$DB_DATABASE"
fi

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R ug+rwx storage bootstrap/cache database || true

RENDER_PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${RENDER_PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${RENDER_PORT}>/" /etc/apache2/sites-available/*.conf /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

php artisan optimize:clear || true
php artisan migrate --path=database/migrations_archive/2026-03-28_mysql_baseline --realpath --force
php artisan db:seed --class=Database\\Seeders\\RenderDemoSeeder --force
php artisan storage:link || true
php artisan view:cache || true

apache2-foreground