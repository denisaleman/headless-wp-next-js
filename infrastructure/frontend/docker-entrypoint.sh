#!/bin/sh
set -e

# If package.json exists but node_modules is missing, install dependencies
if [ -f "package.json" ] && [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

exec "$@"