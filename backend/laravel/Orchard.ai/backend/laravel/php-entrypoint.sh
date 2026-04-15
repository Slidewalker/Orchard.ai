#!/bin/sh
set -e

if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  fi
fi

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q "APP_KEY=\S" .env 2>/dev/null; then
  php artisan key:generate || true
fi

exec "$@"
