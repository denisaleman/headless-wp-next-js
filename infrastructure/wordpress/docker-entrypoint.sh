#!/bin/bash
set -e

if [ -f /var/www/html/web/index.php ]; then
    echo "Bedrock already installed. Ensuring correct permissions for host editing..."
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

# Set ownership of all files to UID 1000 and GID 1000
echo "Setting file ownership to 1000:1000 for host editing..."
chown -R 1000:1000 /var/www/html

# Ensure the group with GID 1000 exists inside the container
if ! getent group 1000 > /dev/null; then
    echo "Creating group with GID 1000..."
    groupadd -g 1000 hostgroup
fi

# Add Apache's user (www-data) to the group 1000 so it can write to uploads
echo "Adding www-data to group 1000..."
usermod -a -G 1000 www-data

# Prepare uploads directory with correct permissions
UPLOADS_DIR="/var/www/html/web/app/uploads"
mkdir -p "$UPLOADS_DIR"
chown 1000:1000 "$UPLOADS_DIR"
chmod 775 "$UPLOADS_DIR"

echo "Starting Apache..."
exec "$@"