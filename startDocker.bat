@echo off
docker compose down
docker volume prune -f
docker compose pull

echo Checking required files and folders...

:: Ensure .docker-compose.yaml exists -> Holds app setup
if not exist docker-compose.yaml (
    echo ERROR: Missing docker compose file
    echo Please download this file before running Docker.
    pause
    exit /b 1
)

:: Ensure app.conf exists -> Holds nginx config
if not exist "docker/etc/nginx/conf.d/default.conf" (
    echo ERROR: Missing docker/etc/nginx/conf.d/default.conf
    echo Please download this file before running Docker.
    pause
    exit /b 1
) else (
    echo Found nginx config
)

:: Ensure caddyfile exists
if not exist "docker/etc/caddy/Caddyfile" (
    echo ERROR: Missing /docker/etc/caddy/Caddyfile
    echo Please download this file before running Docker.
    pause
    exit 1
) else (
    echo Found caddy config
    echo Make sure to replace 'app.test' with your website URL in '/docker/etc/caddy/Caddyfile' and in .env if you have one
)

:: Ensure .env.docker exists -> Holds default environment variables
if not exist "docker/.env.docker" (
    echo ERROR: Missing ./docker/.env.docker
    echo Please download this file before running Docker.
    pause
    exit /b 1
) else (
    echo Found environment variables
)

:: Ensure .env exists
if not exist .env (
    echo .env file not found! Creating from .env.docker...
    copy ".\docker\.env.docker" ".env"
    echo .env file created.
)

:: Ensure the ./data directory exists -> Holds user media
if not exist "data" (
    echo Missing ./data directory...
    mkdir data
    echo ./data directory created.
) else (
    echo Found ./data directory
)

:: Ensure the ./logs directory exists
if not exist "logs" (
    echo Missing ./logs directory! Creating it...
    mkdir -p ./logs
    mkdir -p ./logs/mediaServer
    mkdir -p ./logs/nginx
    mkdir -p ./logs/caddy
    echo ./logs directory created.
) else (
    echo Found ./logs directory
)

echo Setup complete
docker compose up -d
