@echo off
echo Checking required files and folders...

:: Ensure .docker-compose.yaml exists -> Holds app setup
if not exist docker-compose.yaml (
    echo ERROR: Missing docker compose file
    echo Please download this file before running Docker.
    exit /b 1
)

:: Ensure app.conf exists -> Holds nginx config
if not exist "docker/nginx/app.conf" (
    echo ERROR: Missing docker/nginx/app.conf
    echo Please download this file before running Docker.
    exit /b 1
) else (
    echo Found nginx config
    echo Make sure to replace 'app.test' with your website URL in 'docker/nginx/app.conf' if you have one
)

:: Ensure .env.docker exists -> Holds default environment variables
if not exist "docker/.env.docker" (
    echo ERROR: Missing ./docker/.env.docker
    echo Please download this file before running Docker.
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

echo Setup complete
docker-compose up
