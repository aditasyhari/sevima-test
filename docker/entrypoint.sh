#!/bin/sh
set -e

echo "=== Memulai Inisialisasi Aplikasi SEVIMA UKT Gateway ==="

# Tunggu PostgreSQL siap
echo "Menunggu koneksi PostgreSQL ($DB_HOST:$DB_PORT)..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "PostgreSQL siap!"

# Salin .env jika belum ada
if [ ! -f /var/www/.env ]; then
  echo "Menyalin .env.example ke .env..."
  cp /var/www/.env.example /var/www/.env
fi

# Set Application Key jika kosong
if ! grep -q "APP_KEY=base64:" /var/www/.env; then
  echo "Men-generate Application Key..."
  php artisan key:generate --force
fi

# Pastikan permission folder storage dan bootstrap cache aman
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Jalankan migrasi dan seeder otomatis
echo "Menjalankan migrasi database PostgreSQL & seeder institusi..."
php artisan migrate --force
php artisan db:seed --force

# Optimasi cache Laravel untuk performa
echo "Mengoptimalkan cache konfigurasi dan rute..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Aplikasi Siap Dijalankan di Port 8000! ==="
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
