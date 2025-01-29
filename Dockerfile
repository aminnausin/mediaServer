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
    exiftool \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath opcache

# Install Composer
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

RUN echo "error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED" > /usr/local/etc/php/conf.d/error_reporting.ini

# # Create Supervisor directory
# RUN mkdir -p /etc/supervisor/conf.d

# # Copy Supervisor main config
# COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
# # Copy Supervisor configuration
# COPY docker/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# # Copy crontab file
# COPY docker/crontab /etc/crontabs/www-data

# # Ensure correct permissions
# RUN chmod 0644 /etc/crontabs/www-data

# RUN crond

WORKDIR /var/www/html

COPY . .


RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
RUN chmod o+w ./storage/ -R
RUN chmod o+w ./public/ -R

# Copy .env and set up Laravel
RUN cp .env.example .env

RUN php artisan key:generate

RUN php artisan reverb:generate

RUN php artisan storage:link

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
