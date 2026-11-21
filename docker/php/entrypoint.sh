#!/bin/sh
set -e

# If vendor directory is missing, install dependencies
if [ ! -d "/var/www/html/vendor" ] && [ -f "/var/www/html/composer.json" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# If frontend build assets are missing, compile them
if [ ! -d "/var/www/html/public/build" ] && [ -f "/var/www/html/package.json" ]; then
    echo "Building frontend assets..."
    npm ci && npm run build
fi

exec "$@"
