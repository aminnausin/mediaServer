#!/bin/bash

set -e

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "No APP_KEY detected, generating one..."
    php artisan key:generate
else
    echo "APP_KEY already set, skipping key generation."
fi

SHARED_ENV_FILE=/var/www/html/shared/env
LARAVEL_ENV_FILE=/var/www/html/.env

if [ ! -f "$SHARED_ENV_FILE" ]; then

    touch /tmp/shared_env
    chown $SHARED_UID:$SHARED_GID /tmp/shared_env

    echo "Generating REVERB keys..."

    php artisan reverb:generate

    APP_KEY=$(grep -E '^APP_KEY=' /var/www/html/.env | cut -d '=' -f2)
    REVERB_APP_ID=$(grep -E '^REVERB_APP_ID=' /var/www/html/.env | cut -d '=' -f2)
    REVERB_APP_KEY=$(grep -E '^REVERB_APP_KEY=' /var/www/html/.env | cut -d '=' -f2)
    REVERB_APP_SECRET=$(grep -E '^REVERB_APP_SECRET=' /var/www/html/.env | cut -d '=' -f2)

    {
      echo "APP_KEY=$APP_KEY"
      echo "REVERB_APP_ID=$REVERB_APP_ID"
      echo "REVERB_APP_KEY=$REVERB_APP_KEY"
      echo "REVERB_APP_SECRET=$REVERB_APP_SECRET"
    } > /tmp/shared_env

    mv /tmp/shared_env "$SHARED_ENV_FILE"
    echo "Generated keys:"
    cat "$SHARED_ENV_FILE"
else
    echo "Keys already generated."
    while IFS='=' read -r key value; do
        if grep -q "^$key=" "$LARAVEL_ENV_FILE"; then
            # Update existing line
            sed -i "s|^$key=.*|$key=$value|" "$LARAVEL_ENV_FILE"
        else
            # Append if missing
            echo "$key=$value" >> "$LARAVEL_ENV_FILE"
        fi
    done < "$SHARED_ENV_FILE"

    echo "Updated Laravel .env with reverb keys."
fi

# php artisan app:manifest
