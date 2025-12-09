#!/bin/bash
set -e

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --ansi
fi

# Create database file if using SQLite
if grep -q "DB_CONNECTION=sqlite" .env; then
    touch database/database.sqlite 2>/dev/null || true
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Start Laravel development server
echo "Starting Laravel development server on 0.0.0.0:8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
