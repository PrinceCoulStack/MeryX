#!/bin/sh
set -e

mkdir -p /var/www/html/config/jwt

if [ -n "$JWT_PRIVATE_KEY" ]; then
    printf '%s\n' "$JWT_PRIVATE_KEY" > /var/www/html/config/jwt/private.pem
fi

if [ -n "$JWT_PUBLIC_KEY" ]; then
    printf '%s\n' "$JWT_PUBLIC_KEY" > /var/www/html/config/jwt/public.pem
fi

chown -R www-data:www-data /var/www/html/var /var/www/html/config/jwt

php bin/console cache:clear --env=prod --no-debug

exec apache2-foreground
