# =========================================================
# Laravel API - Production Dockerfile (PHP 8.2 FPM + Nginx)
# Runs PHP-FPM and Nginx via supervisord on port 80.
# =========================================================

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --optimize-autoloader

FROM php:8.2-fpm-alpine AS app

ENV APP_ENV=production \
    APP_DEBUG=false

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        openssl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache storage/logs

RUN printf '%s\n' \
'server {' \
'    listen 80;' \
'    server_tokens off;' \
'    client_max_body_size 20m;' \
'    index index.php index.html;' \
'    root /var/www/html/public;' \
'    location / {' \
'        try_files $uri $uri/ /index.php?$query_string;' \
'    }' \
'    location ~ \\.php$ {' \
'        fastcgi_pass 127.0.0.1:9000;' \
'        fastcgi_index index.php;' \
'        include fastcgi_params;' \
'        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'    }' \
'}' > /etc/nginx/http.d/default.conf

RUN printf '%s\n' \
'[supervisord]' \
'nodaemon=true' \
'user=root' \
'' \
'[program:php-fpm]' \
'command=php-fpm -F' \
'autorestart=true' \
'stdout_logfile=/dev/stdout' \
'stdout_logfile_maxbytes=0' \
'stderr_logfile=/dev/stderr' \
'stderr_logfile_maxbytes=0' \
'' \
'[program:nginx]' \
'command=nginx -g "daemon off;"' \
'autorestart=true' \
'stdout_logfile=/dev/stdout' \
'stdout_logfile_maxbytes=0' \
'stderr_logfile=/dev/stderr' \
'stderr_logfile_maxbytes=0' > /etc/supervisord.conf

RUN printf '#!/bin/sh\nset -eu\n\nif [ -f /var/www/html/.env ]; then\n  php artisan config:cache\n  php artisan route:cache\n  php artisan view:cache\nelse\n  echo "No .env file found; skipping Laravel cache warmup."\nfi\n\nif [ "${RUN_MIGRATIONS:-false}" = "true" ]; then\n  php artisan migrate --force\nfi\n\nexec /usr/bin/supervisord -c /etc/supervisord.conf\n' > /entrypoint.sh \
    && chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
