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
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

USER www-data

# =================================================================
# 2: Build Frontend
# =================================================================
FROM node:23-slim AS builder

WORKDIR /var/www/html

COPY . .
RUN npm ci

RUN npm run build-only && \
    npm cache clean --force && \
    rm -rf node_modules

# =================================================================
# 2.5: Puppeteer / Chromium Builder Stage
# =================================================================
FROM node:23-slim AS puppeteer

WORKDIR /puppeteer

RUN npm install puppeteer --omit=dev

RUN npx puppeteer install --chromium-skip-download=false

# =================================================================
# 3: Compile Laravel Image
# =================================================================
FROM serversideup/php:${SERVERSIDEUP_PHP_VERSION}

ARG USER_ID
ARG GROUP_ID
ARG POSTGRES_VERSION

WORKDIR /var/www/html

USER root

RUN docker-php-serversideup-set-id www-data "$USER_ID":"$GROUP_ID" && \
    docker-php-serversideup-set-file-permissions --owner "$USER_ID":"$GROUP_ID" --service nginx

# Install PostgreSQL repository and keys for external access
RUN apk add --no-cache gnupg && \
    mkdir -p /usr/share/keyrings && \
    curl -fSsL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor > /usr/share/keyrings/postgresql.gpg \
    && apk update \
    && apk add --no-cache \
    exiftool \
    ffmpeg \
    git \
    vim \
    nodejs \
    npm

# Useful shell aliases
# RUN echo "alias ll='ls -al'" >> /etc/profile && \
#     echo "alias a='php artisan'" >> /etc/profile && \
#     echo "alias logs='tail -f storage/logs/laravel.log'" >> /etc/profile

# Configure PHP
COPY docker/etc/php/conf.d/zzz-custom-php.ini /usr/local/etc/php/conf.d/zzz-custom-php.ini
ENV PHP_OPCACHE_ENABLE=1

# Configure entrypoint
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d

# Copy default files
RUN mkdir -p /var/www/html/storage/app/public/avatars \
            /var/www/html/storage/app/public/thumbnails \
            /var/www/html/shared

COPY storage/app/public/avatars/default.jpg /var/www/html/storage/app/public/avatars/default.jpg
COPY storage/app/public/thumbnails/default.webp /var/www/html/storage/app/public/thumbnails/default.webp

# Copy dependencies
COPY --from=composer --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=builder --chown=www-data:www-data /var/www/html/public/build ./public/build
COPY --from=puppeteer /puppeteer/node_modules ./node_modules
COPY --chown=www-data:www-data . .

RUN composer dump-autoload \
    && chmod o+w ./storage/ -R
# RUN chmod o+w ./public/ -R

# Copy .env and set up Laravel
COPY --chown=www-data:www-data .env.example .env
RUN chmod -R 755 /var/www/html/.env

# Nginx
# COPY docker/etc/nginx/site-opts.d/http.conf /etc/nginx/site-opts.d/http.conf
COPY docker/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
RUN chown -R www-data:www-data /etc/nginx && \
    chmod -R 755 /etc/nginx

ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_CONFIG_CACHE=false

# RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Clean up unnecessary files
# RUN rm vendor/bin ~/.composer/cache /usr/local/bin/composer

# Make storage folders mountable
VOLUME [ "/var/www/html/storage" ]

USER www-data

# ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
# CMD ["php-fpm"]
