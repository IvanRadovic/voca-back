# =========================================================
# Laravel API - Production Dockerfile (PHP 8.2 FPM + Nginx)
# Runs PHP-FPM, Nginx and the queue worker via supervisord on port 80.
# Designed to be built and deployed from Coolify directly.
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

# Run package discovery now that the full app is present (vendor stage used
# --no-scripts so service providers from Sanctum / dompdf / etc. weren't
# registered in bootstrap/cache/packages.php).
RUN php artisan package:discover --ansi

RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

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
'logfile=/dev/null' \
'logfile_maxbytes=0' \
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
'stderr_logfile_maxbytes=0' \
'' \
'[program:queue-worker]' \
'command=php /var/www/html/artisan queue:work --tries=3 --max-time=3600 --sleep=3 --backoff=10' \
'user=www-data' \
'autorestart=true' \
'stopwaitsecs=60' \
'stdout_logfile=/dev/stdout' \
'stdout_logfile_maxbytes=0' \
'stderr_logfile=/dev/stderr' \
'stderr_logfile_maxbytes=0' > /etc/supervisord.conf

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

# Laravel 11 ships a built-in health endpoint at /up.
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["/entrypoint.sh"]
