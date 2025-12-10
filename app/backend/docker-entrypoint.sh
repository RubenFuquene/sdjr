#!/bin/bash
set -e

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Parse DATABASE_URL for Railway if provided
if [ ! -z "$DATABASE_URL" ]; then
    echo "Configuring database from DATABASE_URL..."
    
    # Detect database type from URL scheme
    if [[ $DATABASE_URL == mysql://* ]]; then
        DB_CONNECTION="mysql"
        # Extract components from mysql://user:password@host:port/database
        DB_HOST=$(echo $DATABASE_URL | sed -n 's/.*@\([^:]*\).*/\1/p')
        DB_PORT=$(echo $DATABASE_URL | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
        DB_DATABASE=$(echo $DATABASE_URL | sed -n 's/.*\/\([^?]*\).*/\1/p')
        DB_USERNAME=$(echo $DATABASE_URL | sed -n 's/.*:\/\/\([^:]*\):.*/\1/p')
        DB_PASSWORD=$(echo $DATABASE_URL | sed -n 's/.*:\/\/[^:]*:\([^@]*\)@.*/\1/p')
    elif [[ $DATABASE_URL == postgres://* ]]; then
        DB_CONNECTION="pgsql"
        # Extract components from postgres://user:password@host:port/database
        DB_HOST=$(echo $DATABASE_URL | sed -n 's/.*@\([^:]*\).*/\1/p')
        DB_PORT=$(echo $DATABASE_URL | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
        DB_DATABASE=$(echo $DATABASE_URL | sed -n 's/.*\/\([^?]*\).*/\1/p')
        DB_USERNAME=$(echo $DATABASE_URL | sed -n 's/.*:\/\/\([^:]*\):.*/\1/p')
        DB_PASSWORD=$(echo $DATABASE_URL | sed -n 's/.*:\/\/[^:]*:\([^@]*\)@.*/\1/p')
    else
        echo "Warning: Unsupported DATABASE_URL scheme. Keeping default config."
        DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)
    fi
    
    # Update .env with parsed values if we have a supported connection
    if [ ! -z "$DB_CONNECTION" ] && [[ "$DB_CONNECTION" == "mysql" || "$DB_CONNECTION" == "pgsql" ]]; then
        sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" .env
        sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
        sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    fi
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

# Wait for database to be ready (for Railway)
if [ ! -z "$DATABASE_URL" ]; then
    echo "Waiting for database connection..."
    timeout=30
    while ! php artisan db:show >/dev/null 2>&1; do
        timeout=$((timeout - 1))
        if [ $timeout -eq 0 ]; then
            echo "Database connection timeout!"
            exit 1
        fi
        echo "Waiting for database... ($timeout)"
        sleep 1
    done
    echo "Database connection established!"
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Start Laravel server (Railway uses PORT env variable)
PORT=${PORT:-8000}
echo "Starting Laravel server on 0.0.0.0:$PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
