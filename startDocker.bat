@echo off

SET "SHARED_VOLUME_NAME=mediaserver-shared-env"
SET "VOLUME_UID=9999"
SET "VOLUME_GID=9999"
SET "ENV_FILE=./.env"
SET "FALLBACK_DEFAULT_DOMAIN=app.test"
SET "NGINX_CONF_FILE=docker\etc\nginx\conf.d\default.conf"
echo.
echo ============================================
echo           mediaServer Docker Setup
echo ============================================
echo.

SET "IS_CI_MODE=false"
IF /I "%~1"=="--auto-default" (
    SET "IS_CI_MODE=true"
    call :ColorText "[INFO] " BLUE
    echo "Auto-default mode enabled via command-line argument.""
    SHIFT /1
) ELSE IF DEFINED CI (
    SET "IS_CI_MODE=true"
    call :ColorText "[INFO] " BLUE
    echo "Auto-default mode enabled (CI/CD environment detected - CI variable)."
) ELSE IF DEFINED GITHUB_ACTIONS (
    SET "IS_CI_MODE=true"
    call :ColorText "[INFO] " BLUE
    echo "Auto-default mode enabled (CI/CD environment detected - GITHUB_ACTIONS variable)."
)
echo.

call :ColorText "[STEP 1/6] " Yellow
echo Verifying required files and folders...
echo.

:: Check for docker-compose.yaml
if not exist docker-compose.yaml (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker-compose.yaml' file.
    echo Please ensure this file is present in the root directory.
    pause
    goto :end
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
    goto :end
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
    goto :end
) else (
    call :ColorText "[FOUND]" Green
    echo 'Caddyfile' configuration file.
    echo NOTE: Make sure to replace 'app.test' with your website URL in:
    echo       - '/docker/etc/caddy/Caddyfile' or wherever your reverse proxy is
)
echo.

:: Check for .env.docker
if not exist "docker/.env.docker" (
    call :ColorText "[ERROR]" Red
    echo Missing 'docker/.env.docker' file.
    echo Please ensure this file is present in the correct directory.
    pause
    goto :end
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
        goto :end
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
        goto :end
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
        goto :end
    )
    call :ColorText "[SUCCESS]" Green
    echo 'logs' directory created.
) else (
    call :ColorText "[FOUND]" Green
    echo 'logs' directory.
)
echo.

:: Ensure caddy directory exists
if not exist "caddy/data" (
    call :ColorText "[INFO]" Blue
    echo Missing 'caddy' directory. Creating it...
    echo.
    mkdir "caddy"
    mkdir "caddy/data"
    mkdir "caddy/config"
    if errorlevel 1 (
        call :ColorText "[ERROR]" Red
        echo Failed to create 'caddy' directory.
        pause
        goto :end
    )
    call :ColorText "[SUCCESS]" Green
    echo 'caddy' directory created.
) else (
    call :ColorText "[FOUND]" Green
    echo 'caddy' directory.
)
echo.

call :ColorText "[STEP 2/6] " Yellow
echo Setting up user config...
echo.

setlocal ENABLEDELAYEDEXPANSION
SET "CURRENT_APP_HOST="
IF EXIST "%ENV_FILE%" (
    FOR /F "tokens=1* delims==" %%a IN ('findstr /b "APP_HOST=" "%ENV_FILE%"') DO (
        SET "CURRENT_APP_HOST=%%b"
    )
)

SET "CURRENT_DOMAIN_DEFAULT=%FALLBACK_DEFAULT_DOMAIN%"

IF NOT "!CURRENT_APP_HOST!"=="" (
    REM Remove quotes
    SET "TEMP_DOMAIN_FROM_HOST=!CURRENT_APP_HOST:"=!"
    IF NOT "!TEMP_DOMAIN_FROM_HOST!"=="" (
        SET "CURRENT_DOMAIN_DEFAULT=!TEMP_DOMAIN_FROM_HOST!"
    )
)
ENDLOCAL & SET "CURRENT_DOMAIN_DEFAULT=%CURRENT_DOMAIN_DEFAULT%"

REM Ask for domain name, or use default in CI/CD mode
IF "%IS_CI_MODE%"=="true" (
    SET "USER_DOMAIN=%CURRENT_DOMAIN_DEFAULT%"
    call :ColorText "[INFO] " BLUE
    echo Automatically using default domain: %USER_DOMAIN%
) ELSE (
    call :ColorText "[CONFIG] " YELLOW
    echo "Enter your app domain (URL)..."
    echo "Current value: %CURRENT_DOMAIN_DEFAULT% (Press Enter to use this)..."
    echo.
    set /p USER_DOMAIN="Domain: "
)
echo.

REM Use current default if user input is empty
IF "%USER_DOMAIN%"=="" (
    SET "APP_HOST=%CURRENT_DOMAIN_DEFAULT%"
) ELSE (
    SET "APP_HOST=%USER_DOMAIN%"
)

call :ColorText "[INFO] " BLUE
echo Setting APP_HOST to: %APP_HOST%
echo.

REM Update APP_HOST in .env file

SET "TEMP_ENV_FILE=%ENV_FILE%.tmp"
(
    for /f "tokens=1,* delims=:" %%a in ('findstr /n "^" "%ENV_FILE%"') do (
        set "line=%%b"
        setlocal ENABLEDELAYEDEXPANSION
        if "!line:~0,9!"=="APP_HOST=" (
            echo APP_HOST="%APP_HOST%"
        ) else (
            echo(!line!
        )
        endlocal
    )
) > "%TEMP_ENV_FILE%"

IF %ERRORLEVEL% NEQ 0 (
    echo.
    call :ColorText "[ERROR]" RED
    echo Failed to create temporary .env file.
    echo Please check script permissions.
    pause
    goto :end
)

MOVE /Y "%TEMP_ENV_FILE%" "%ENV_FILE%" >nul

IF %ERRORLEVEL% NEQ 0 (
    echo.
    call :ColorText "[ERROR]" RED
    echo Failed to replace original .env file.
    echo Please check script permissions.
    pause
    goto :end
) ELSE (
    call :ColorText "[SUCCESS] " GREEN
    echo APP_HOST updated in %ENV_FILE%.
)
echo.

REM Update Nginx configuration

SET "SCRIPT_DIR=%~dp0"
SET "NGINX_CONF_FILE=%SCRIPT_DIR%%NGINX_CONF_FILE%"

SET "TEMP_NGINX_CONF_FILE=%NGINX_CONF_FILE%.tmp"

(
    for /f "tokens=1,* delims=:" %%a in ('findstr /n "^" "%NGINX_CONF_FILE%"') do (
        if "%%b"=="" (
        echo.
        ) else (
            echo %%b | findstr /i "valid_referers 127.0.0.1," >nul
            if errorlevel 1 (
                echo %%b
            ) else (
                echo         valid_referers 127.0.0.1, %APP_HOST%;
            )
        )
    )
) > "%TEMP_NGINX_CONF_FILE%"

ver >nul

IF NOT EXIST "%TEMP_NGINX_CONF_FILE%" (
    call :ColorText "[ERROR]" RED
    echo Temp Nginx config file not created.
    pause
    goto :end
)

MOVE /Y "%TEMP_NGINX_CONF_FILE%" "%NGINX_CONF_FILE%" >nul

IF %ERRORLEVEL% NEQ 0 (
    call :ColorText "[ERROR]" RED
    echo Failed to replace original Nginx config file.
    echo Please check script permissions.
    pause
    goto :end
) ELSE (
    call :ColorText "[SUCCESS] " GREEN
    echo Nginx valid_referers updated to include: %APP_HOST%
)
echo.

call :ColorText "[STEP 3/6] " Yellow
echo Stopping and cleaning up Existing mediaServer Docker containers...
echo .

docker compose down
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to bring down Docker containers.
    echo Please check your Docker setup and try again.
    pause
    goto :end
)
echo.
call :ColorText "[SUCCESS]" Green
echo Docker containers stopped and cleaned up.
echo.

call :ColorText "[STEP 4/6] " Yellow
echo Pruning Docker volumes...
echo.
docker volume prune -f
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to prune Docker volumes.
    echo Please check your Docker setup and try again.
    pause
    goto :end
)
echo.
call :ColorText "[SUCCESS]" Green
echo Docker volumes pruned.
echo.

call :ColorText "[STEP 5/6] " Yellow
echo Pulling latest Docker images...
echo.
docker compose pull
if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to pull Docker images.
    echo Please check your internet connection and Docker setup.
    pause
    goto :end
) else (
    echo.
    call :ColorText "[SUCCESS]" Green
    echo Docker images pulled successfully.
    echo.
)

call :ColorText "[STEP 6/6] " Yellow
echo Building docker compose...
echo.

call :ColorText "[INFO] " BLUE
echo Checking for shared Docker volume '%SHARED_VOLUME_NAME%'...
docker volume inspect %SHARED_VOLUME_NAME%
echo.
IF %ERRORLEVEL% NEQ 0 (
    call :ColorText "[INFO] " BLUE
    echo Shared volume '%SHARED_VOLUME_NAME%' does not exist. Creating it...
    echo.
    docker volume create "%SHARED_VOLUME_NAME%"
    IF %ERRORLEVEL% NEQ 0 (
        call :ColorText "[ERROR]" RED
        echo Failed to create shared volume '%SHARED_VOLUME_NAME%'.
        echo Please check Docker daemon status and permissions.
        pause
        goto :end
    ) ELSE (
        call :ColorText "[SUCCESS] " GREEN
        echo Shared volume '%SHARED_VOLUME_NAME%' created.
    )
) ELSE (
    call :ColorText "[FOUND] " GREEN
    echo Shared volume '%SHARED_VOLUME_NAME%' exists.
)
echo.

call :ColorText "[INFO] " BLUE
echo Setting permissions on shared volume '%SHARED_VOLUME_NAME%'...
docker run --rm ^
  -v "%SHARED_VOLUME_NAME%:/shared" ^
  alpine sh -c "mkdir -p /shared && chown -R %VOLUME_UID%:%VOLUME_GID% /shared && chmod 775 /shared"

IF %ERRORLEVEL% NEQ 0 (
    echo.
    call :ColorText "[ERROR]" RED
    echo Failed to set shared volume permissions.
    echo Please check your Docker volume and user/group ID configurations.
    pause
    goto :end
)
echo.

call :ColorText "[SUCCESS] " GREEN
echo Shared volume permissions set.
echo.

docker compose up -d

if errorlevel 1 (
    echo.
    call :ColorText "[ERROR]" Red
    echo Failed to build docker compose.
    echo Please check your configuration.
    pause
    goto :end
)
echo.
echo ============================================
echo          SETUP COMPLETED SUCCESSFULLY!
echo ============================================
echo.
echo Your mediaServer will be available at https://%APP_HOST%
echo.
echo To add audio or video to your server, put the files in ./data/media organised by /LIBRARY/FOLDER/VIDEO.mp4

:end

set arg0=%0
if [%arg0:~2,1%]==[:] pause
exit /b

:: Function to print colored text
:ColorText
setlocal
set "text=%~1"
set "color=%~2"

if "%color%"=="" (
    call :ColorText "[ERROR]" Red
    goto :end
)

powershell -NoProfile -Command "Write-Host '%text%' -ForegroundColor %color%"
exit /b 1
