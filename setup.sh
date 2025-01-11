#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

git pull

npm i
composer install

cp .env.example .env

php artisan key:generate

npm run build

php artisan install:broadcasting

echo "Setup a database connection in the .env file and then run php artisan migrate"
