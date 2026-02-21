#!/bin/sh
set -e

echo "[init] Fixing shared volume permissions..."

SHARED_DIR=/var/www/html/shared

mkdir -p "$SHARED_DIR"

# Fix ownership for mounted named volume
chown -R www-data:www-data "$SHARED_DIR" || true
chmod 775 "$SHARED_DIR" || true
