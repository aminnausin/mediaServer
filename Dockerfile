FROM node:22 AS builder

WORKDIR /var/www/html

COPY package.json ./
COPY package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build-only

FROM jkaninda/nginx-php-fpm:8.3

WORKDIR /var/www/html

COPY . .

RUN composer Install
RUN chmod o+w ./storage/ -R
RUN chmod o+w ./public/ -R

RUN cp .env.example .env

RUN php artisan key:generate

RUN php artisan reverb:generate

RUN php artisan storage:link

# RUN php artisan migrate

COPY --from=builder /var/www/html/public/build ./public/build

# Set permissions for the entrypoint script

# RUN cp .env.example .env

# RUN php artisan key:generate

# RUN php artisan reverb:generate

# RUN php artisan storage:link

# RUN php artisan migrate

ENV DOCUMENT_ROOT=/var/www/html/public
# # syntax=docker/dockerfile:1

# FROM php:8.4-fpm

# RUN apt-get update && apt-get install -y \
#     build-essential \
#     libpng-dev \
#     libjpeg-dev \
#     libwebp-dev \
#     libxpm-dev \
#     libfreetype6-dev \
#     libzip-dev \
#     zip \
#     unzip \
#     git \
#     bash \
#     fcgiwrap \
#     libmcrypt-dev \
#     libonig-dev \
#     libpq-dev \
#     ffmpeg \
#     exiftool \
#     && rm -rf /var/lib/apt/lists/*

# # Install PHP extensions
# RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
#     && docker-php-ext-install gd \
#     && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath opcache

# # Install Composer
# COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# # Copy existing application directory contents
# COPY . /var/www/html/

# # Set ownership and permissions for the /var/www/html directory to www-data
# RUN chown -R www-data:www-data /var/www/html/

# USER www-data

# EXPOSE 9000

# CMD ["php-fpm"]
