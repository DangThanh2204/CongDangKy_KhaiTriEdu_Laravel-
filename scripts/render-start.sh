#!/usr/bin/env bash
set -e

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
fi

DB_DRIVER="${DB_CONNECTION:-}"
if [ -z "$DB_DRIVER" ]; then
  if [ "${RENDER_FALLBACK_SQLITE:-true}" = "true" ]; then
    DB_DRIVER="sqlite"
    export DB_CONNECTION="sqlite"
  else
    echo "DB_CONNECTION is not set and sqlite fallback is disabled."
    exit 1
  fi
fi

if [ "$DB_DRIVER" = "sqlite" ]; then
  export DB_DATABASE="${DB_DATABASE:-${RENDER_SQLITE_PATH:-/tmp/render.sqlite}}"
  mkdir -p "$(dirname "$DB_DATABASE")"
  touch "$DB_DATABASE"
  chmod 666 "$DB_DATABASE" || true
fi

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache database || true
chmod -R ug+rwx storage bootstrap/cache database || true

RENDER_PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${RENDER_PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${RENDER_PORT}>/" /etc/apache2/sites-available/*.conf /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

php artisan optimize:clear || true

mysql_exec() {
  MYSQL_PWD="${DB_PASSWORD:-}" mysql --protocol=TCP -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" "$@"
}

mysql_ready() {
  mysql_exec -Nse "SELECT 1" "$DB_DATABASE" >/dev/null 2>&1
}

mysql_table_count() {
  mysql_exec -Nse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE();" "$DB_DATABASE"
}

mysql_table_exists() {
  local table_name="$1"
  local result
  result="$(mysql_exec -Nse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '${table_name}';" "$DB_DATABASE" 2>/dev/null || echo 0)"
  [ "${result:-0}" != "0" ]
}

mysql_missing_core_tables() {
  local missing=()
  local table_name

  for table_name in users settings courses classes course_enrollments payments wallet_transactions course_reviews system_logs; do
    if ! mysql_table_exists "$table_name"; then
      missing+=("$table_name")
    fi
  done

  printf '%s' "${missing[*]}"
}

mysql_column_exists() {
  local table_name="$1"
  local column_name="$2"
  local result
  result="$(mysql_exec -Nse "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '${table_name}' AND column_name = '${column_name}';" "$DB_DATABASE" 2>/dev/null || echo 0)"
  [ "${result:-0}" != "0" ]
}

mysql_missing_core_columns() {
  local missing=()
  local entry
  local table_name
  local column_name

  for entry in     users:is_verified     courses:status     course_enrollments:status     course_enrollments:completed_at     payments:status     wallet_transactions:metadata     wallet_transactions:status     course_reviews:created_at     system_logs:category     system_logs:action     system_logs:created_at
  do
    table_name="${entry%%:*}"
    column_name="${entry##*:}"
    if mysql_table_exists "$table_name" && ! mysql_column_exists "$table_name" "$column_name"; then
      missing+=("${table_name}.${column_name}")
    fi
  done

  printf '%s' "${missing[*]}"
}

if [ "$DB_DRIVER" = "mysql" ] || [ "$DB_DRIVER" = "mariadb" ]; then
  : "${DB_HOST:?DB_HOST is required when using MySQL/MariaDB on Render}"
  : "${DB_DATABASE:?DB_DATABASE is required when using MySQL/MariaDB on Render}"
  : "${DB_USERNAME:?DB_USERNAME is required when using MySQL/MariaDB on Render}"
  export DB_PORT="${DB_PORT:-3306}"

  echo "Waiting for MySQL/MariaDB connection..."
  for attempt in $(seq 1 30); do
    if mysql_ready; then
      break
    fi
    if [ "$attempt" -eq 30 ]; then
      echo "Unable to connect to MySQL/MariaDB after multiple attempts."
      exit 1
    fi
    sleep 2
  done

  if [ "${RENDER_RESET_DATABASE:-false}" = "true" ]; then
    echo "Resetting MySQL/MariaDB database before bootstrap..."
    php artisan db:wipe --force
  fi

  table_count="$(mysql_table_count || echo 0)"
  missing_core_tables="$(mysql_missing_core_tables || true)"
  missing_core_columns="$(mysql_missing_core_columns || true)"

  if [ "$table_count" = "0" ] || [ -n "$missing_core_tables" ] || [ -n "$missing_core_columns" ]; then
    if [ "$table_count" != "0" ] && { [ -n "$missing_core_tables" ] || [ -n "$missing_core_columns" ]; }; then
      [ -n "$missing_core_tables" ] && echo "Detected missing core tables in MySQL/MariaDB: $missing_core_tables"
      [ -n "$missing_core_columns" ] && echo "Detected missing core columns in MySQL/MariaDB: $missing_core_columns"
      echo "Rebuilding database from canonical SQL source before serving the app..."
      php artisan db:wipe --force
    fi

    if [ "${RENDER_IMPORT_SQL_DUMP:-true}" = "true" ] && [ -f "${RENDER_SQL_DUMP_PATH:-/var/www/html/khaitriedu.sql}" ]; then
      echo "Importing SQL dump into public MySQL/MariaDB database..."
      mysql_exec "$DB_DATABASE" < "${RENDER_SQL_DUMP_PATH:-/var/www/html/khaitriedu.sql}"
    elif [ -f "${RENDER_SCHEMA_DUMP_PATH:-/var/www/html/database/schema/mysql-schema.sql}" ]; then
      echo "Importing MySQL schema dump into public MySQL/MariaDB database..."
      mysql_exec "$DB_DATABASE" < "${RENDER_SCHEMA_DUMP_PATH:-/var/www/html/database/schema/mysql-schema.sql}"
    else
      echo "Database bootstrap failed because no SQL source was found."
      exit 1
    fi
  fi

  echo "Running Laravel migrations to align imported/public MySQL schema with current code..."
  php artisan migrate --force

  if [ "${RENDER_SEED_DEMO:-false}" = "true" ]; then
    echo "Seeding/updating Render demo data..."
    php artisan db:seed --class=Database\\Seeders\\RenderDemoSeeder --force
  fi
else
  php artisan migrate --path=database/migrations_archive/2026-03-28_mysql_baseline --realpath --force
  php artisan db:seed --class=Database\\Seeders\\RenderDemoSeeder --force
fi

php artisan storage:link || true
php artisan view:cache || true

exec apache2-foreground