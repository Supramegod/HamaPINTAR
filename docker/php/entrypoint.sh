#!/bin/sh
set -e
cd /var/www/html

[ -f .env ] || cp .env.example .env
[ -d vendor ] || composer install --no-interaction --prefer-dist
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force

# Tunggu MySQL siap lalu jalankan migrasi (koneksi dibaca dari .env)
attempt=0
until php artisan migrate --force --no-interaction; do
    attempt=$((attempt + 1))
    [ "$attempt" -ge 30 ] && echo "Database tidak dapat dihubungi." && exit 1
    echo "Menunggu database... ($attempt)"
    sleep 2
done

exec "$@"
