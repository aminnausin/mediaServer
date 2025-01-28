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
