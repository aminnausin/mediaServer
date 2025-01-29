#!/bin/bash


# Exit immediately if a command exits with a non-zero status
set -e

echo "Waiting for PostgreSQL to be ready..."

# Loop until PostgreSQL is ready
until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT 1" > /dev/null 2>&1; do
    echo "PostgreSQL is unavailable - sleeping..."
    sleep 2
done

php artisan migrate

# Start Supervisor in the background
# echo "Starting Supervisor..."

exec "$@"
