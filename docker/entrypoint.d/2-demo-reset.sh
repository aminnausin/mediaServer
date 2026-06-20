#!/bin/bash

set -e
if [ "$AUTORUN_DEMO_RESET" = "true" ]; then
    php artisan demo:reset
fi
