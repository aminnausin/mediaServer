#!/bin/bash

# Define color variables
RED='\033[31m'
GREEN='\033[32m'
YELLOW='\033[33m'
BLUE='\033[34m'
RESET='\033[0m' # Reset color

echo
echo "============================================"
echo "          mediaServer Docker Setup          "
echo "============================================"
echo

echo -e "${YELLOW}[STEP 1/5]${RESET} Verifying required files and folders..."
echo

# Check for docker-compose.yaml
if [ ! -f "docker-compose.yaml" ]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker-compose.yaml' file."
    echo "Please ensure this file is present in the root directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'docker-compose.yaml' file."
fi
echo

# Check for nginx configuration
if [ ! -f "docker/etc/nginx/conf.d/default.conf" ]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/etc/nginx/conf.d/default.conf' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'nginx' configuration file."
fi
echo

# Check for Caddyfile
if [ ! -f "docker/etc/caddy/Caddyfile" ]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/etc/caddy/Caddyfile' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'Caddyfile' configuration file."
    echo "NOTE: Make sure to replace 'app.test' with your website URL in:"
    echo "      - '/docker/etc/caddy/Caddyfile'"
    echo "      - '.env'"
fi
echo

# Check for .env.docker
if [ ! -f "docker/.env.docker" ]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/.env.docker' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
fi

# Create .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo -e "${BLUE}[INFO]${RESET} '.env' file not found. Creating from '.env.docker'..."
    cp "docker/.env.docker" ".env"
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create '.env' file."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} '.env' file created."
else
    echo -e "${GREEN}[FOUND]${RESET} '.env' file."
fi
echo

# Ensure data directory exists
if [ ! -d "data/media" ]; then
    echo -e "${BLUE}[INFO]${RESET} Missing 'data/media' directory. Creating it..."
    echo
    mkdir -p "data/media"
    mkdir -p "data/avatars"
    mkdir -p "data/thumbnails"
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create 'data' directory."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} 'data' directory created."
else
    echo -e "${GREEN}[FOUND]${RESET} 'data' directory."
fi
echo

# Ensure logs directory exists
if [ ! -d "logs" ]; then
    echo -e "${BLUE}[INFO]${RESET} Missing 'logs' directory. Creating it..."
    echo
    mkdir -p "logs/mediaServer"
    mkdir -p "logs/nginx"
    mkdir -p "logs/caddy"
    if [ $? -ne 0 ]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create 'logs' directory."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} 'logs' directory created."
else
    echo -e "${GREEN}[FOUND]${RESET} 'logs' directory."
fi
echo

echo -e "${YELLOW}[STEP 2/5]${RESET} Stopping and cleaning up Existing mediaServer Docker containers..."
docker compose down
if [ $? -ne 0 ]; then
    echo
    echo -e "${RED}[ERROR]${RESET} Failed to bring down Docker containers."
    echo "Please check your Docker setup and try again."
    exit 1
fi
echo
echo -e "${GREEN}[SUCCESS]${RESET} Docker containers stopped and cleaned up."
echo

echo -e "${YELLOW}[STEP 3/5]${RESET} Pruning Docker volumes..."
echo
docker volume prune -f
if [ $? -ne 0 ]; then
    echo
    echo -e "${RED}[ERROR]${RESET} Failed to prune Docker volumes."
    echo "Please check your Docker setup and try again."
    exit 1
fi
echo
echo -e "${GREEN}[SUCCESS]${RESET} Docker volumes pruned."
echo

echo -e "${YELLOW}[STEP 4/5]${RESET} Pulling latest Docker images..."
echo
docker compose pull
if [ $? -ne 0 ]; then
    echo
    echo -e "${RED}[ERROR]${RESET} Failed to pull Docker images."
    echo "Please check your internet connection and Docker setup."
    exit 1
else
    echo
    echo -e "${GREEN}[SUCCESS]${RESET} Docker images pulled successfully."
    echo
fi

echo -e "${YELLOW}[STEP 5/5]${RESET} Building docker compose..."
echo
docker compose up -d

if [ $? -ne 0 ]; then
    echo
    echo -e "${RED}[ERROR]${RESET} Failed to build docker compose."
    echo "Please check your configuration."
    exit 1
fi

echo "============================================"
echo -e "${GREEN}          SETUP COMPLETED SUCCESSFULLY!${RESET}"
echo "============================================"
echo
echo "Your mediaServer will be available at https://app.test OR https://YOUR.URL"
echo
echo "To add audio or video to your server, put the files in ./data/media organised by /CATEGORY/FOLDER/VIDEO.mp4"
