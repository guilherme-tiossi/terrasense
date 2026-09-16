#!/bin/sh
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist
fi

php-fpm -D
nginx -g "daemon off;"
