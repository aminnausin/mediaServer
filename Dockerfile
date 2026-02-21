# Versions
# https://hub.docker.com/r/serversideup/php/tags?name=8.4-fpm-nginx-alpine
ARG SERVERSIDEUP_PHP_VERSION=8.4-fpm-nginx-alpine

# Add user/group
ARG USER_ID=1000
ARG GROUP_ID=1000

# Git state
ARG GIT_COMMIT=unknown
ARG GIT_TAG=unknown

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
FROM node:24-alpine AS builder

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
FROM node:24-slim AS puppeteer

WORKDIR /app

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

RUN npm install puppeteer --ignore-scripts --omit=dev && \
    npm cache clean --force

# =================================================================
# 3: Compile Laravel Image
# =================================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION}

ARG USER_ID
ARG GROUP_ID
ARG GIT_COMMIT=unknown
ARG GIT_TAG=unknown

WORKDIR /var/www/html

USER root

# Install runtime dependencies
RUN docker-php-serversideup-set-id www-data "$USER_ID":"$GROUP_ID" && \
    docker-php-serversideup-set-file-permissions --owner "$USER_ID":"$GROUP_ID" --service nginx && \
    apk add --no-cache \
        ca-certificates \
        chromium \
        exiftool \
        ffmpeg \
        freetype \
        harfbuzz \
        nodejs \
        nss \
        ttf-freefont && \
    rm -rf /var/cache/apk/*

# Configure PHP
COPY docker/etc/php/conf.d/zzz-custom-php.ini /usr/local/etc/php/conf.d/zzz-custom-php.ini
ENV PHP_OPCACHE_ENABLE=1

# Configure entrypoint
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d

# Configure storage and generate chrome config directories
RUN mkdir -p /var/www/html/shared \
    /var/www/html/storage/app/public/avatars \
    /var/www/html/storage/app/public/thumbnails \
    /var/www/html/storage/app/private \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/chrome/.config

# Copy default images
COPY storage/app/public/avatars/default.jpg /var/www/html/storage/app/public/avatars/default.jpg
COPY storage/app/public/thumbnails/default.webp /var/www/html/storage/app/public/thumbnails/default.webp

# Copy dependencies
COPY --from=composer --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=builder --chown=www-data:www-data /var/www/html/public/build ./public/build
COPY --from=puppeteer /app/node_modules ./node_modules
COPY --chown=www-data:www-data . .

# Copy .env and set up Laravel
COPY --chown=www-data:www-data .env.example .env

# Copy Nginx config
COPY docker/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Set permissions for Laravel runtime and generate git info
# Setup NGINX
RUN echo "${GIT_COMMIT}" > ./COMMIT && \
    echo "${GIT_TAG}" > ./VERSION && \
    chown www-data:www-data ./COMMIT ./VERSION && \
    mkdir -p /var/www/html/bootstrap/cache && \
    chown www-data:www-data /var/www/html/bootstrap/cache && \
    composer dump-autoload --optimize --classmap-authoritative && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/shared && \
    chmod -R 775 /var/www/html/storage /var/www/html/shared && \
    chmod 644 /var/www/html/.env && \
    chmod g+s /var/www/html/storage/app/private \
             /var/www/html/storage/app/public \
             /var/www/html/storage/logs \
             /var/www/html/shared && \
    chown -R www-data:www-data /etc/nginx && \
    chmod -R 775 /etc/nginx && \
    rm -rf /tmp/* /root/.npm /root/.cache /home/www-data/.cache /usr/share/man /usr/share/doc /var/cache/apk/*

ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=false

USER www-data
