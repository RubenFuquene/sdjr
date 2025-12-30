#!/bin/bash
set -e

ENV_FILE=".env"
DB_WAIT_TIMEOUT=${DB_WAIT_TIMEOUT:-60}
DB_WAIT_REQUIRED=0

update_env_var() {
    local key="$1"
    local value="$2"
    if grep -q "^$key=" "$ENV_FILE"; then
        sed -i "s|^$key=.*|$key=$value|" "$ENV_FILE"
    else
        echo "$key=$value" >> "$ENV_FILE"
    fi
}

bootstrap_env_file() {
    if [ ! -f "$ENV_FILE" ]; then
        echo "Creating .env file from .env.example..."
        cp .env.example "$ENV_FILE"
    fi
}

configure_database_from_url() {
    local url="$1"
    [ -z "$url" ] && return 1

    echo "Configuring database from DATABASE_URL..."

    local scheme=$(php -r 'echo parse_url($argv[1], PHP_URL_SCHEME);' "$url")
    local connection=""
    case "$scheme" in
        mysql)
            connection="mysql"
            ;;
        postgres|postgresql)
            connection="pgsql"
            ;;
        *)
            echo "Warning: Unsupported DATABASE_URL scheme. Keeping default config."
            return 1
            ;;
    esac

    local host=$(php -r 'echo parse_url($argv[1], PHP_URL_HOST);' "$url")
    local port=$(php -r 'echo parse_url($argv[1], PHP_URL_PORT);' "$url")
    local db=$(php -r 'echo ltrim(parse_url($argv[1], PHP_URL_PATH),"/");' "$url")
    local user=$(php -r 'echo parse_url($argv[1], PHP_URL_USER);' "$url")
    local pass=$(php -r 'echo parse_url($argv[1], PHP_URL_PASS);' "$url")

    if [ -z "$port" ]; then
        port=$([ "$connection" = "mysql" ] && echo "3306" || echo "5432")
    fi

    update_env_var DB_CONNECTION "$connection"
    update_env_var DB_HOST "$host"
    update_env_var DB_PORT "$port"
    update_env_var DB_DATABASE "$db"
    update_env_var DB_USERNAME "$user"
    update_env_var DB_PASSWORD "$pass"

    DB_WAIT_REQUIRED=1
}

configure_database_from_mysql_env() {
    [ -z "$MYSQLHOST" ] && return 1

    echo "Configuring database from Railway MySQL env vars..."

    local host="$MYSQLHOST"
    local port="${MYSQLPORT:-3306}"
    local db="${MYSQL_DATABASE:-${MYSQLDATABASE:-sdjr}}"
    local user="${MYSQLUSER:-${MYSQL_USERNAME:-sdjr_user}}"
    local pass="${MYSQLPASSWORD:-${MYSQL_PASSWORD:-sdjr_password}}"

    update_env_var DB_CONNECTION "mysql"
    update_env_var DB_HOST "$host"
    update_env_var DB_PORT "$port"
    update_env_var DB_DATABASE "$db"
    update_env_var DB_USERNAME "$user"
    update_env_var DB_PASSWORD "$pass"

    DB_WAIT_REQUIRED=1
}

determine_wait_requirement() {
    local connection=$(grep '^DB_CONNECTION=' "$ENV_FILE" | cut -d '=' -f2)
    if [[ "$connection" == "mysql" || "$connection" == "pgsql" ]]; then
        DB_WAIT_REQUIRED=1
    fi
}

get_app_environment() {
    # Get APP_ENV from environment variable or .env file
    # Handles quoted values and whitespace
    local env_value="${APP_ENV:-$(grep '^APP_ENV=' "$ENV_FILE" 2>/dev/null | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)}"
    # Convert to lowercase for case-insensitive comparison
    echo "$env_value" | tr '[:upper:]' '[:lower:]'
}

wait_for_database() {
    if [ "$DB_WAIT_REQUIRED" -ne 1 ]; then
        return
    fi

    local host=$(grep '^DB_HOST=' "$ENV_FILE" | cut -d '=' -f2)
    local port=$(grep '^DB_PORT=' "$ENV_FILE" | cut -d '=' -f2)

    if [ -z "$host" ]; then
        echo "DB host not defined, skipping wait."
        return
    fi

    echo "Waiting for database connection ($host:${port:-unknown})..."
    local timeout=$DB_WAIT_TIMEOUT

    until php artisan db:show >/dev/null 2>&1; do
        timeout=$((timeout - 1))
        if [ $timeout -le 0 ]; then
            echo "Database connection timeout!"
            exit 1
        fi
        sleep 1
    done

    echo "Database connection established!"
}

generate_app_key_if_needed() {
    if ! grep -q "APP_KEY=base64:" "$ENV_FILE"; then
        echo "Generating application key..."
        php artisan key:generate --ansi
    fi
}

ensure_sqlite_file_if_needed() {
    if grep -q "DB_CONNECTION=sqlite" "$ENV_FILE"; then
        touch database/database.sqlite 2>/dev/null || true
    fi
}

run_migrations() {
    # Determine application environment, preferring the current environment
    # variable and falling back to the value in the .env file if needed.
    local app_env=$(get_app_environment)

    # Support common production environment names (production, prod)
    if [[ "$app_env" == "production" || "$app_env" == "prod" ]]; then
        echo "Production environment detected, running non-destructive migrations..."
        php artisan migrate --force --no-interaction
    else
        echo "Non-production environment detected, running fresh migrations..."
        php artisan migrate:fresh --force --no-interaction
    fi
}

start_server() {
    local port=${PORT:-8000}
    echo "Starting Laravel server on 0.0.0.0:$port..."
    exec php artisan serve --host=0.0.0.0 --port=$port
}

main() {
    bootstrap_env_file

    # Normalize URLs provided by hosting platforms
    if [ -z "$DATABASE_URL" ] && [ ! -z "$MYSQL_URL" ]; then
        DATABASE_URL="$MYSQL_URL"
    fi

    configure_database_from_url "$DATABASE_URL" || true

    if [ $DB_WAIT_REQUIRED -ne 1 ]; then
        configure_database_from_mysql_env || true
    fi

    determine_wait_requirement
    generate_app_key_if_needed
    ensure_sqlite_file_if_needed
    wait_for_database
    run_migrations
    start_server
}

main "$@"
