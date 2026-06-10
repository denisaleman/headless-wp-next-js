FROM wordpress:cli-php8.2

USER root

# Create scripts directory and copy script
COPY scripts/ /scripts/
RUN chmod +x /scripts/setup.sh

# Install MariaDB client (optional)
RUN apk add --no-cache mariadb-client

# Keep container alive
ENTRYPOINT ["tail", "-f", "/dev/null"]