#!/bin/bash

# --- Configuration ---
SHARED_VOLUME_NAME="mediaserver-shared-env"
VOLUME_UID="9999"
VOLUME_GID="9999"
ENV_FILE="./.env" # Path to your .env file
FALLBACK_DEFAULT_DOMAIN="app.test" # Default if APP_URL is not found in .env
NGINX_CONF_FILE="docker/etc/nginx/conf.d/default.conf" # Path to Nginx config
# --- End Configuration ---

# --- Define color variables ---
RED='\033[31m'
GREEN='\033[32m'
YELLOW='\033[33m'
BLUE='\033[34m'
RESET='\033[0m' # Reset color
# --- End color variables ---

echo
echo "============================================"
echo "          mediaServer Docker Setup          "
echo "============================================"
echo

# --- Check for CI/CD or auto-default mode ---
IS_CI_MODE=false
if [[ "$1" == "--auto-default" ]]; then
    IS_CI_MODE=true
    echo "${BLUE}[INFO]${RESET} Auto-default mode enabled via command-line argument."
    shift # Consume the argument if it's explicitly passed
elif [[ -n "$CI" || -n "$GITHUB_ACTIONS" || -n "$GITLAB_CI" || -n "$JENKINS_URL" ]]; then
    IS_CI_MODE=true
    echo "${BLUE}[INFO]${RESET} Auto-default mode enabled (CI/CD environment detected)."
fi
echo
# --- End CI/CD check ---

echo -e "${YELLOW}[STEP 1/6]${RESET} Verifying required files and folders..."
echo

# Check for docker-compose.yaml
if [[ ! -f "docker-compose.yaml" ]]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker-compose.yaml' file."
    echo "Please ensure this file is present in the root directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'docker-compose.yaml' file."
fi
echo

# Check for nginx configuration
if [[ ! -f "$NGINX_CONF_FILE" ]]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/etc/nginx/conf.d/default.conf' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'nginx' configuration file."
fi
echo

# Check for Caddyfile
if [[ ! -f "docker/etc/caddy/Caddyfile" ]]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/etc/caddy/Caddyfile' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
else
    echo -e "${GREEN}[FOUND]${RESET} 'Caddyfile' configuration file."
    echo "NOTE: Make sure to replace 'app.test' with your website URL in:"
    echo "      - '/docker/etc/caddy/Caddyfile' or wherever your reverse proxy is"
fi
echo

# Check for .env.docker
if [[ ! -f "docker/.env.docker" ]]; then
    echo -e "${RED}[ERROR]${RESET} Missing 'docker/.env.docker' file."
    echo "Please ensure this file is present in the correct directory."
    exit 1
fi

# Create .env if it doesn't exist
if [[ ! -f "$ENV_FILE" ]]; then
    echo -e "${BLUE}[INFO]${RESET} '.env' file not found. Creating from '.env.docker'..."
    cp "docker/.env.docker" "$ENV_FILE"
    if [[ $? -ne 0 ]]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create '.env' file."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} '.env' file created."
else
    echo -e "${GREEN}[FOUND]${RESET} '.env' file."
fi
echo

# Ensure data/app subdirectories exists
if [[ ! -d "data/media" ]] || [[ ! -d "data/avatars" ]] || [[ ! -d "data/thumbnails" ]] || [[ ! -d "app" ]]; then
    echo -e "${BLUE}[INFO]${RESET} One or more 'data/app' subdirectories are missing. Creating them..."
    echo
    mkdir -p data/media data/avatars data/thumbnails app
    echo -e "${GREEN}[SUCCESS]${RESET} 'data/app' subdirectories created."
else
    echo -e "${GREEN}[FOUND]${RESET} 'data/app' subdirectories."
fi
echo

# Ensure permissions are set for data directories
sudo chown -R 9999:9999 data app
sudo chmod -R 775 data app
if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to create 'data/app' subdirectories or set permissions."
    exit 1
fi

# Ensure logs directory exists
if [[ ! -d "logs" ]]; then
    echo -e "${BLUE}[INFO]${RESET} Missing 'logs' directory. Creating it..."
    echo
    mkdir -p "logs/mediaServer"
    mkdir -p "logs/nginx"
    mkdir -p "logs/caddy"
    sudo chown -R 9999:9999 ./logs/nginx
    sudo chown -R 9999:9999 ./logs/mediaServer
    sudo chmod -R 755 logs
    if [[ $? -ne 0 ]]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create 'logs' directory."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} 'logs' directory created."
else
    echo -e "${GREEN}[FOUND]${RESET} 'logs' directory."
fi
echo

# Ensure caddy directory exists
if [[ ! -d "caddy/data" ]]; then
    echo -e "${BLUE}[INFO]${RESET} Missing 'caddy' directory. Creating it..."
    echo
    mkdir -p "caddy/data"
    mkdir -p "caddy/config"
    sudo chown -R 1000:1000 ./caddy
    if [[ $? -ne 0 ]]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create 'caddy' directory."
        exit 1
    fi
    echo -e "${GREEN}[SUCCESS]${RESET} 'caddy' directory created."
else
    echo -e "${GREEN}[FOUND]${RESET} 'caddy' directory."
fi
echo

echo -e "${YELLOW}[STEP 2/6]${RESET} Setting up user config..."
echo

# Get existing APP_HOST and APP_PORT or use defaults
CURRENT_APP_HOST=""
CURRENT_APP_PORT=""
if [[ -f "$ENV_FILE" ]]; then
    CURRENT_APP_HOST=$(grep "^APP_HOST=" "$ENV_FILE" | sed -E 's/^APP_HOST="?([^"]*)"?/\1/')
    CURRENT_APP_PORT=$(grep "^APP_PORT=" "$ENV_FILE" | sed -E 's/^APP_PORT="?([^"]*)"?/\1/')
fi

CURRENT_DOMAIN_DEFAULT="${FALLBACK_DEFAULT_DOMAIN}"
if [[ -n "$CURRENT_APP_HOST" ]]; then
    CURRENT_DOMAIN_DEFAULT="$CURRENT_APP_HOST"
fi

if $IS_CI_MODE; then
    USER_DOMAIN="$CURRENT_DOMAIN_DEFAULT"
    echo "${BLUE}[INFO]${RESET} Automatically using default domain: ${USER_DOMAIN}"
    echo
else
    # Ask for domain name with a default
    echo -e "${YELLOW}[CONFIG]${RESET} Enter your app domain (URL)..."
    echo -e "${YELLOW}[CONFIG]${RESET} Current value: ${CURRENT_DOMAIN_DEFAULT} (Press enter to use this)..."
    echo
    read -p "Domain: " USER_DOMAIN
    echo
fi

APP_HOST="${USER_DOMAIN:-$CURRENT_DOMAIN_DEFAULT}" # Use current default if user input is empty
APP_PORT="${CURRENT_APP_PORT:-8080}" # Use current default if user input is empty

echo -e "${BLUE}[INFO]${RESET} Setting APP_HOST to: ${APP_HOST}"
echo

# Update APP_HOST in .env file using sed
sed -i "s|^APP_HOST=.*|APP_HOST=\"${APP_HOST}\"|" "$ENV_FILE"
if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to update APP_HOST in ${ENV_FILE}."
    echo "Please check the .env file format or script permissions."
    exit 1
else
    echo -e "${GREEN}[SUCCESS]${RESET} APP_HOST updated in ${ENV_FILE}."
fi
echo

ESCAPED_APP_HOST=$(echo "$APP_HOST" | sed 's/\./\\./g') # Escape dots

sed -i "s#valid_referers 127.0.0.1, .*;#valid_referers 127.0.0.1, ${ESCAPED_APP_HOST};#" "$NGINX_CONF_FILE"

if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to update valid_referers in ${NGINX_CONF_FILE}."
    echo "Please check the Nginx config file format or script permissions."
    exit 1
else
    echo -e "${GREEN}[SUCCESS]${RESET} Nginx valid_referers updated to include: ${APP_HOST}"
fi
echo

echo -e "${YELLOW}[STEP 3/6]${RESET} Stopping and cleaning up Existing mediaServer Docker containers..."
echo

docker compose down
if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to bring down Docker containers."
    echo "Please check your Docker setup and try again."
    exit 1
fi
echo -e "${GREEN}[SUCCESS]${RESET} Docker containers stopped and cleaned up."
echo

echo -e "${YELLOW}[STEP 4/6]${RESET} Pruning Docker volumes..."
echo
docker volume prune -f
if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to prune Docker volumes."
    echo "Please check your Docker setup and try again."
    exit 1
fi
echo -e "${GREEN}[SUCCESS]${RESET} Docker volumes pruned."
echo

echo -e "${YELLOW}[STEP 5/6]${RESET} Pulling latest Docker images..."
echo
docker compose pull
if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to pull Docker images."
    echo "Please check your internet connection and Docker setup."
    exit 1
else
    echo -e "${GREEN}[SUCCESS]${RESET} Docker images pulled successfully."
    echo
fi

echo -e "${YELLOW}[STEP 6/6]${RESET} Building docker compose..."
echo

# Check for shared docker volume
if ! docker volume inspect "$SHARED_VOLUME_NAME" &>/dev/null; then
    echo -e "${BLUE}[INFO]${RESET} Shared volume '$SHARED_VOLUME_NAME' does not exist. Creating it..."
    docker volume create "$SHARED_VOLUME_NAME"
    if [[ $? -ne 0 ]]; then
        echo -e "${RED}[ERROR]${RESET} Failed to create shared volume '$SHARED_VOLUME_NAME'."
        echo "Please check Docker daemon status and permissions."
        exit 1
    else
        echo -e "${GREEN}[SUCCESS]${RESET} Shared volume '$SHARED_VOLUME_NAME' created."
    fi
else
    echo -e "${GREEN}[FOUND]${RESET} Shared volume '$SHARED_VOLUME_NAME' exists."
fi
echo


# Create a temporary container to set permissions on shared volume
echo -e "${BLUE}[INFO]${RESET} Setting permissions on shared volume '$SHARED_VOLUME_NAME'..."
echo
docker run --rm \
  -v "${SHARED_VOLUME_NAME}:/shared" \
  alpine sh -c "mkdir -p /shared && chown -R ${VOLUME_UID}:${VOLUME_GID} /shared && chmod 775 /shared && echo 'Volume permissions set.'"

if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to set shared volume permissions."
    echo "Please check your Docker volume and user/group ID configurations."
    exit 1
fi
echo -e "${GREEN}[SUCCESS]${RESET} Shared volume permissions set."
echo

docker compose up -d

if [[ $? -ne 0 ]]; then
    echo -e "${RED}[ERROR]${RESET} Failed to build docker compose."
    echo "Please check your configuration."
    exit 1
fi

echo "============================================"
echo -e "${GREEN}          SETUP COMPLETED SUCCESSFULLY!${RESET}"
echo "============================================"
echo
echo "Your mediaServer will be available at https://$APP_HOST or http://127.0.0.1:$APP_PORT"
echo
echo "To add audio or video to your server, put the files in ./data/media organised by /LIBRARY/FOLDER/VIDEO.mp4"

if [[ "$APP_HOST" == "app.test" ]]; then
    echo
    echo "${YELLOW}[WARNING]${RESET} The domain '$APP_HOST' is not publicly resolvable."
    echo "You may need to add the following line to your /etc/hosts file:"
    echo
    echo "  127.0.0.1    $APP_HOST"
    echo
    echo "Run the following command with sudo if desired:"
    echo "echo '127.0.0.1 $APP_HOST' | sudo tee -a /etc/hosts"
    echo
fi
