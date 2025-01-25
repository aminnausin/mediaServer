@echo off
setlocal EnableDelayedExpansion

:: Exit immediately if a command exits with a non-zero status
set -e

:: Pull the latest changes from the repository
git pull

:: Install Node.js dependencies
npm install

:: Install Composer dependencies
composer install

:: Copy the example environment configuration file
copy .env.example .env

:: Generate a new application key
php artisan key:generate

:: Build frontend assets
npm run build

:: Install broadcasting dependencies
php artisan install:broadcasting

echo "Setup a database connection in the .env file and then run 'php artisan migrate'"

endlocal
@echo on
