FROM php:8.3.16-fpm-alpine AS composer

RUN apk update && apk add --no-cache \
    bash \
    git \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    postgresql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath opcache

# Install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.lock composer.lock
COPY composer.json composer.json
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

FROM php:8.3.16-fpm-alpine AS backend

RUN apk update && apk add --no-cache \
    bash \
    git \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    postgresql-client \
    ffmpeg \
    exiftool

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath opcache

WORKDIR /var/www/html

COPY . .

COPY --from=composer /var/www/html/vendor ./vendor

RUN chmod o+w ./storage/ -R
RUN chmod o+w ./public/ -R

# Copy .env and set up Laravel
RUN cp .env.example .env && php artisan storage:link

FROM node:22 AS builder

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

# Copy application files again
COPY --from=backend /var/www/html /var/www/html

RUN npm run build-only && \
    npm cache clean --force && \
    rm -rf node_modules


FROM backend AS final

COPY --from=builder /var/www/html/public/build ./public/build

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Clean up unnecessary files
RUN rm -rf node_modules vendor/bin ~/.composer/cache /usr/local/bin/composer

# Make storage folders mountable
VOLUME [ "/var/www/html/storage" ]

USER www-data

EXPOSE 9000
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
# CMD ["supervisord", "-c", "/etc/supervisord.conf"]
CMD ["php-fpm"]
