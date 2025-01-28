#!/bin/bash

# RUN npm i
# npm run build-only
# npm cache clean --force
# rm -rf node_modules

# "Installing Composer (PHP) dependencies"
# composer install
# "Setup environment variables"
cp .env.example .env

# "Setup Laravel app key"
php artisan key:generate

# "Setup Reverb app keys"
php artisan reverb:generate

# Link Storage
php artisan storage:link

# "Setup a database connection in the .env file and then run 'php artisan migrate'"
# "Finally run 'npm run vite:php' to start the queue and websocket servers in addition to your web server (nginx)"
php artisan migrate
