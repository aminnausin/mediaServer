@echo off

:: Pull the latest changes from the repository
git pull

echo Install Node.js dependencies
npm i

echo Installing Composer (PHP) dependencies
composer i

echo Setup environment variables
copy .env.example .env

echo Setup Laravel app key
php artisan key:generate

echo Setup Reverb app keys
php artisan reverb:generate

echo Build Frontend
npm run build


echo Setup a database connection in the .env file and then run 'php artisan migrate'
echo Finally run 'npm run vite:php' to start the queue and websocket servers in addition to your web server (nginx)

@echo on
