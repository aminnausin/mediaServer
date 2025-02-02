#!/bin/bash

# Ensure the script is executed in the project root
cd "$(dirname "$0")"

echo "🔹 Checking required files and folders..."

# Ensure .docker-compose.yaml exists -> Holds app setup
if [ ! -f "docker-compose.yaml" ]; then
    echo "❌ ERROR: Missing docker compose file"
    echo "➡️  Please download this file before running Docker."
    exit 1
fi

# Ensure app.conf exists
if [ ! -f "./docker/nginx/app.conf" ]; then
    echo "❌ ERROR: Missing ./docker/nginx/app.conf"
    echo "➡️  Please download this file before running Docker."
    exit 1
else
    echo "✅ Found nginx config"
    echo "Make sure to replace 'app.test' with your website URL in 'docker/nginx/app.conf' if you have one"
fi

# Ensure docker env exists
if [ ! -f "./docker/.env.docker" ]; then
    echo "❌ ERROR: Missing ./docker/.env.docker"
    echo "➡️  Please download this file before running Docker."
    exit 1
else
    echo "✅ Found environment variables"
fi

# Check if .env exists, otherwise create it from .env.example
if [ ! -f ".env" ]; then
    echo "⚠️  .env file not found! Creating from .env.docker..."
    cp ./docker/.env.docker .env
    echo "✅ .env file created."
fi

# Ensure the ./data directory exists
if [ ! -d "./data" ]; then
    echo "⚠️  Missing ./data directory! Creating it..."
    mkdir -p ./data
    echo "✅ ./data directory created."
else
    echo "✅ Found ./data directory"
fi

echo "✅ Setup complete"
docker-compose up
