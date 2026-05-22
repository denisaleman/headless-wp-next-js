#!/bin/sh
set -e

# Always install dependencies (idempotent, uses cache if nothing changed)
if [ -f "package.json" ]; then
    echo "Ensuring dependencies are installed..."
    npm install
fi

exec "$@"