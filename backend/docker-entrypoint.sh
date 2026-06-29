#!/bin/sh
set -e

echo "Waiting for MySQL..."
until php artisan migrate --force 2>/dev/null; do
  echo "MySQL not ready, retrying in 3s..."
  sleep 3
done

echo "Seeding database..."
php artisan db:seed --force 2>/dev/null || true

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=8000
