#!/bin/sh
# Container entrypoint. Runs once per container start before supervisord
# takes over.
set -eu

cd /var/www/html

# 1. Sanity check on APP_KEY (warn loudly, don't crash — easier to debug).
if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo ">> WARNING: APP_KEY is not set. Generate one with 'php artisan key:generate --show'"
    echo ">> and add it to the Coolify environment, or this container will fail to boot."
fi

# 2. Persistent-volume safety: re-chown writable dirs in case a fresh mount
# came in owned by root.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rw storage bootstrap/cache 2>/dev/null || true

# 3. Public storage symlink (idempotent — skip if it already points correctly).
if [ ! -L public/storage ]; then
    php artisan storage:link --force 2>/dev/null || true
fi

# 4. Cache warm-up. config:clear first so a stale cached config from the
# previous image never survives across deploys.
php artisan config:clear   >/dev/null 2>&1 || true
php artisan route:clear    >/dev/null 2>&1 || true
php artisan view:clear     >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Optional migration on boot. Default false — set RUN_MIGRATIONS=true in
# Coolify so each deploy applies new migrations.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo ">> Running database migrations…"
    php artisan migrate --force
fi

# 6. Hand off to supervisord (nginx + php-fpm + queue worker).
exec /usr/bin/supervisord -c /etc/supervisord.conf
