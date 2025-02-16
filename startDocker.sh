#!/bin/bash

# Ensure the script is executed in the project root
cd "$(dirname "$0")"

echo "🔹 Checking required files and folders..."

# Ensure .docker-compose.yaml exists -> Holds app setup
if [ ! -f "docker-compose.yaml" ]; then
    echo "ERROR: Missing docker compose file"
    echo "Please download this file before running Docker."
    pause
    exit 1
fi

# Ensure default.conf exists
if [ ! -f "./docker/etc/nginx/conf.d/default.conf" ]; then
    echo "ERROR: Missing docker/etc/nginx/conf.d/default.conf"
    echo "Please download this file before running Docker."
    pause
    exit 1
else
    echo "Found nginx config"
fi

# Ensure caddyfile exists
if [ ! -f "./docker/etc/caddy/caddyfile" ]; then
    echo "ERROR: Missing /docker/etc/caddy/caddyfile"
    echo "Please download this file before running Docker."
    pause
    exit 1
else
    echo "Found caddy config"
    echo "Make sure to replace 'app.test' with your website URL in '/docker/etc/caddy/caddyfile' if you have one"
fi

# Ensure docker env exists
if [ ! -f "./docker/.env.docker" ]; then
    echo "ERROR: Missing docker/.env.docker"
    echo "Please download this file before running Docker."
    pause
    exit 1
else
    echo "Found environment variables"
fi

# Check if .env exists, otherwise create it from .env.example
if [ ! -f ".env" ]; then
    echo ".env file not found! Creating from .env.docker..."
    cp ./docker/.env.docker .env
    echo ".env file created."
fi

# Ensure the ./data directory exists
if [ ! -d "./data" ]; then
    echo "Missing ./data directory! Creating it..."
    mkdir -p ./data
    echo "./data directory created."
else
    echo "Found ./data directory"
fi

echo "Setup complete"
docker-compose up -d
