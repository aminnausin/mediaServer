@echo off

echo.
echo ============================================
echo           mediaServer Docker Setup
echo ============================================
echo.

echo [STEP 1] Verifying required files and folders...
echo.

:: Check for docker-compose.yaml
if not exist docker-compose.yaml (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker-compose.yaml' file.
    echo Please ensure this file is present in the root directory.
    pause
    exit /b 1
) else (
    call :ColorText "[FOUND]" Green
    echo 'docker-compose.yaml' file.
)
echo.

:: Check for nginx configuration
if not exist "docker/etc/nginx/conf.d/default.conf" (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker/etc/nginx/conf.d/default.conf' file.
    echo Please ensure this file is present in the correct directory.
    pause
    exit /b 1
) else (
    call :ColorText "[FOUND]" Green
    echo 'nginx' configuration file.
)
echo.

:: Check for Caddyfile
if not exist "docker/etc/caddy/Caddyfile" (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker/etc/caddy/Caddyfile' file.
    echo Please ensure this file is present in the correct directory.
    pause
    exit /b 1
) else (
    call :ColorText "[FOUND]" Green
    echo 'Caddyfile' configuration file.
    echo NOTE: Make sure to replace 'app.test' with your website URL in:
    echo       - '/docker/etc/caddy/Caddyfile'
    echo       - '.env'
)
echo.

:: Check for .env.docker
if not exist "docker/.env.docker" (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker/.env.docker' file.
    echo Please ensure this file is present in the correct directory.
    pause
    exit /b 1
)

:: Create .env if it doesn't exist
if not exist .env (
    call :ColorText "[INFO]" Blue
    echo '.env' file not found. Creating from '.env.docker'...
    copy ".\docker\.env.docker" ".env"
    if errorlevel 1 (
        call :ColorText "[ERROR]" Red
        echo Failed to create '.env' file.
        pause
        exit /b 1
    )
    call :ColorText "[SUCCESS]" Green
    echo '.env' file created.
) else (
    call :ColorText "[FOUND]" Green
    echo '.env' file.
)
echo.

:: Ensure data directory exists
if not exist "data/media" (
    call :ColorText "[INFO]" Blue
    echo Missing 'data/media' directory. Creating it...
    echo.
    mkdir "data"
    mkdir "data/media"
    mkdir "data/avatars"
    mkdir "data/thumbnails"
    if errorlevel 1 (
        call :ColorText "[ERROR]" Red
        echo Failed to create 'data' directory.
        pause
        exit /b 1
    )
    call :ColorText "[SUCCESS]" Green
    echo 'data' directory created.
) else (
    call :ColorText "[FOUND]" Green
    echo 'data' directory.
)
echo.

:: Ensure logs directory exists
if not exist "logs" (
    call :ColorText "[INFO]" Blue
    echo Missing 'logs' directory. Creating it...
    echo.
    mkdir "logs"
    mkdir "logs/mediaServer"
    mkdir "logs/nginx"
    mkdir "logs/caddy"
    if errorlevel 1 (
        call :ColorText "[ERROR]" Red
        echo Failed to create 'logs' directory.
        pause
        exit /b 1
    )
    call :ColorText "[SUCCESS]" Green
    echo 'logs' directory created.
) else (
    call :ColorText "[FOUND]" Green
    echo 'logs' directory.
)
echo.

echo [STEP 2] Stopping and cleaning up Existing mediaServer Docker containers...
docker compose down
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to bring down Docker containers.
    echo Please check your Docker setup and try again.
    pause
    exit /b 1
)
echo.
call :ColorText "[SUCCESS]" Green
echo Docker containers stopped and cleaned up.
echo.

echo [STEP 3] Pruning Docker volumes...
echo.
docker volume prune -f
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to prune Docker volumes.
    echo Please check your Docker setup and try again.
    pause
    exit /b 1
)
echo.
call :ColorText "[SUCCESS]" Green
echo Docker volumes pruned.
echo.

echo [STEP 4] Pulling latest Docker images...
echo.
docker compose pull
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to pull Docker images.
    echo Please check your internet connection and Docker setup.
    pause
    exit /b 1
) else (
    echo.
    call :ColorText "[SUCCESS]" Green
    echo Docker images pulled successfully.
    echo.

    echo [STEP 5] Building docker compose...
    echo.
    docker compose up -d

    if errorlevel 1 (
        echo.
        call :ColorText "[ERROR]" Red
        echo Failed to build docker compose.
        echo Please check your configuration.
        pause
        exit /b 1
    )
    echo ============================================
    echo          SETUP COMPLETED SUCCESSFULLY!
    echo ============================================
    echo.
    echo Your mediaServer will be available at https://app.test OR https://YOUR.URL
    echo.
    echo To add audio or video to your server, put the files in ./data/media organised by /CATEGORY/FOLDER/VIDEO.mp4
)

pause
:: End of main script logic
goto :eof

:: Function to print colored text
:ColorText
setlocal
set "text=%~1"
set "color=%~2"

if "%color%"=="" (
    call :ColorText "[ERROR]" Red
    exit /b 1
)

powershell -NoProfile -Command "Write-Host '%text%' -ForegroundColor %color%"
exit /b
