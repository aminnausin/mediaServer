# Versions
# https://hub.docker.com/r/serversideup/php/tags?name=8.4-fpm-nginx-alpine
ARG SERVERSIDEUP_PHP_VERSION=8.4-fpm-nginx-alpine
# https://www.postgresql.org/support/versioning/
ARG POSTGRES_VERSION=17

# Add user/group
ARG USER_ID=9999
ARG GROUP_ID=9999

# =================================================================
# 1: Install composer dependencies
# =================================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION} AS composer

USER root

ARG USER_ID
ARG GROUP_ID

RUN docker-php-serversideup-set-id www-data "$USER_ID":"$GROUP_ID" && \
    docker-php-serversideup-set-file-permissions --owner "$USER_ID":"$GROUP_ID" --service nginx

WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader --classmap-authoritative

USER www-data

# =================================================================
# 2: Build Frontend
# =================================================================
FROM node:24.2-alpine AS builder

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=production

RUN npm ci

RUN npm run build-only && \
    npm cache clean --force && \
    rm -rf node_modules

# =================================================================
# 2.5: Puppeteer / Dependencies
# =================================================================
FROM node:24.2-slim AS puppeteer

WORKDIR /app

# RUN npm install puppeteer --production && npm cache clean --force
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

RUN npm install puppeteer --ignore-scripts --omit=dev && \
    npm cache clean --force

# ENV PUPPETEER_CACHE_DIR=/app/puppeteer-cache

# RUN npm install puppeteer && npm prune --omit=dev \
#     && npx puppeteer browsers install chrome

# =================================================================
# 3: Compile Laravel Image
# =================================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION}

ARG USER_ID
ARG GROUP_ID
ARG POSTGRES_VERSION

WORKDIR /var/www/html

USER root

# Install PostgreSQL repository and keys for external access
RUN docker-php-serversideup-set-id www-data "$USER_ID":"$GROUP_ID" && \
    docker-php-serversideup-set-file-permissions --owner "$USER_ID":"$GROUP_ID" --service nginx && \
    apk add --no-cache gnupg && \
    mkdir -p /usr/share/keyrings && \
    curl --proto "=https" -fSsL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor > /usr/share/keyrings/postgresql.gpg \
    && apk update \
    && apk add --no-cache \
    ca-certificates \
    chromium \
    exiftool \
    ffmpeg \
    freetype \
    harfbuzz \
    nodejs \
    npm \
    nss \
    ttf-freefont && \
    rm -rf /var/cache/apk/*

# Configure PHP
COPY docker/etc/php/conf.d/zzz-custom-php.ini /usr/local/etc/php/conf.d/zzz-custom-php.ini
ENV PHP_OPCACHE_ENABLE=1

# Configure entrypoint
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d

# Copy default files
RUN mkdir -p /var/www/html/storage/app/public/avatars \
            /var/www/html/storage/app/public/thumbnails \
            /var/www/html/shared

COPY storage/app/public /var/www/html/storage/app/public

# Copy dependencies
COPY --from=composer --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=builder --chown=www-data:www-data /var/www/html/public/build ./public/build
COPY --from=puppeteer /app/node_modules ./node_modules
COPY --chown=www-data:www-data . .

# Copy .env and set up Laravel
COPY --chown=www-data:www-data .env.example .env

# Generate manifest, set permissions for Laravel runtime and generate chrome config directories
RUN composer dump-autoload && \
    apk add --no-cache git && \
    git config --global --add safe.directory /var/www/html && \
    php artisan app:manifest && \
    rm -rf .git && \
    apk del git && \
    mkdir -p /var/www/html/storage/app/chrome/.config && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R g+ws /var/www/html/storage && \
    chmod 644 /var/www/html/.env && \
    chmod -R 775 /var/www/html/storage/app/chrome

# Nginx
COPY docker/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
RUN chown -R www-data:www-data /etc/nginx && \
    chmod -R 755 /etc/nginx && \
    rm -rf /tmp/* /root/.npm /root/.cache /home/www-data/.cache /usr/share/man /usr/share/doc /var/cache/apk/*

ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=false

# Make storage folders mountable
VOLUME [ "/var/www/html/storage" ]

USER www-data
