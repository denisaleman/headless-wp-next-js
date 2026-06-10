#!/bin/sh
set -e

cd /var/www/html

# Wait for database using wp db check (now it will find the config)
echo "Waiting for database..."
MAX_RETRIES=30
RETRY_COUNT=0
until wp db check --allow-root >/dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT+1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo "Database not ready after $MAX_RETRIES attempts – exiting."
        exit 1
    fi
    echo "Waiting for database (attempt $RETRY_COUNT)..."
    sleep 3
done
echo "Database is ready."


# Install WordPress if not already installed
if ! wp core is-installed --allow-root; then
    echo "Installing WordPress..."
    wp core install \
        --url="http://localhost" \
        --title="Headless WordPress" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@example.com \
        --allow-root
    echo "Installation complete."
else
    echo "WordPress already installed."
fi


# Set permalinks
echo "Setting permalink structure to /%postname%/..."
wp rewrite structure '/%postname%/' --allow-root


echo "Setup finished. WordPress is ready."