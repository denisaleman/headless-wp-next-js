#!/bin/bash
set -e

if [ -f /var/www/html/vendor/autoload.php ]; then
    echo "Bedrock already installed. Ensuring correct permissions..."
else
    echo "No Bedrock installation found. Installing..."
    TEMP_DIR=$(mktemp -d)
    composer create-project roots/bedrock "$TEMP_DIR" --no-interaction
    shopt -s dotglob
    cp -rn "$TEMP_DIR/." /var/www/html/
    rm -rf "$TEMP_DIR"
    if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
        echo "Created .env from example. Please edit it with your credentials."
    fi
    echo "Bedrock installation complete."
fi

THEME_DIR="/var/www/html/web/app/themes/headless-news"
if [ -f "$THEME_DIR/composer.json" ] && [ -d "$THEME_DIR" ]; then
    cd "$THEME_DIR"
    if [ -f vendor/autoload.php ]; then
        echo "Theme dependencies already installed."
    else
        composer install --no-interaction
        echo "Theme dependencies installed."
    fi
    cd /var/www/html
else
    echo "No theme composer.json found, skipping theme dependencies."
fi

# Ownership for host editing
chown -R 1000:1000 /var/www/html

# Ensure group 1000 exists and add www-data (PHP-FPM user) to it
if ! getent group 1000 > /dev/null; then
    groupadd -g 1000 hostgroup
fi
usermod -a -G 1000 www-data

# Uploads directory: owned by 1000:1000, group writable
UPLOADS_DIR="/var/www/html/web/app/uploads"
mkdir -p "$UPLOADS_DIR"
chown 1000:1000 "$UPLOADS_DIR"
chmod 775 "$UPLOADS_DIR"

echo "Starting PHP-FPM..."
exec "$@"