#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="${APP_URL:-$RENDER_EXTERNAL_URL}"
  export VNPAY_RETURN_URL="${VNPAY_RETURN_URL:-$RENDER_EXTERNAL_URL/payments/vnpay/return}"
  export VNPAY_IPN_URL="${VNPAY_IPN_URL:-$RENDER_EXTERNAL_URL/payments/vnpay/ipn}"
fi

if ! php -r '
$key = getenv("APP_KEY") ?: "";
if ($key === "") {
    exit(1);
}
if (str_starts_with($key, "base64:")) {
    $decoded = base64_decode(substr($key, 7), true);
    exit(is_string($decoded) && strlen($decoded) === 32 ? 0 : 1);
}
exit(strlen($key) === 32 ? 0 : 1);
'; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "APP_KEY was missing or invalid, generated a valid Laravel key for this container."
  echo "Tip: set APP_KEY once in Render Environment to keep sessions stable across redeploys."
fi

export DB_CONNECTION="${DB_CONNECTION:-mongodb}"

if [ "$DB_CONNECTION" != "mongodb" ]; then
  echo "Render bootstrap now expects DB_CONNECTION=mongodb."
  exit 1
fi

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R ug+rwx storage bootstrap/cache database || true

RENDER_PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${RENDER_PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${RENDER_PORT}>/" /etc/apache2/sites-available/*.conf /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

php artisan optimize:clear || true

if ! php -m | grep -qi '^mongodb$'; then
  echo "The mongodb PHP extension is required but not loaded."
  exit 1
fi

if [ -z "${DB_URI:-}" ] && [ -z "${DB_DATABASE:-}" ]; then
  echo "Set DB_URI or DB_DATABASE for MongoDB before starting the app."
  exit 1
fi

bootstrap_args=()

if [ "${RENDER_RESET_DATABASE:-false}" = "true" ]; then
  bootstrap_args+=(--fresh)
fi

if [ "${RENDER_SEED_DEMO:-false}" = "true" ]; then
  bootstrap_args+=(--seed-demo)
fi

if [ "${#bootstrap_args[@]}" -gt 0 ]; then
  php artisan mongodb:bootstrap "${bootstrap_args[@]}"
fi

# Re-run the demo seeder on every boot. Uses updateOrCreate so it only refreshes
# the demo records (slug-keyed) without touching admin-created content.
php artisan db:seed --class='Database\Seeders\RenderDemoSeeder' --force || true

php artisan storage:link || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

exec apache2-foreground
