#!/bin/bash

set -e
if [ "$APP_ENV" = "demo" ]; then
    php artisan demo:reset
fi
