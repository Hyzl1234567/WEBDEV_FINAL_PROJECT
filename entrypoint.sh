#!/bin/sh
set -e

echo "🌿 EcoBrew — Starting up..."

# ── 0. Create .env file from Railway environment variables ────────────────────
echo "APP_ENV=prod" > /var/www/html/.env
echo "APP_SECRET=${APP_SECRET}" >> /var/www/html/.env
echo "DATABASE_URL=${DATABASE_URL}" >> /var/www/html/.env
echo "JWT_PASSPHRASE=${JWT_PASSPHRASE}" >> /var/www/html/.env
echo "JWT_SECRET_KEY=${JWT_SECRET_KEY}" >> /var/www/html/.env
echo "JWT_PUBLIC_KEY=${JWT_PUBLIC_KEY}" >> /var/www/html/.env
echo "MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN}" >> /var/www/html/.env
echo "MAILER_DSN=${MAILER_DSN}" >> /var/www/html/.env
echo "CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN}" >> /var/www/html/.env
echo "GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}" >> /var/www/html/.env
echo "GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}" >> /var/www/html/.env
echo "PUSHER_APP_ID=${PUSHER_APP_ID}" >> /var/www/html/.env
echo "PUSHER_KEY=${PUSHER_KEY}" >> /var/www/html/.env
echo "PUSHER_SECRET=${PUSHER_SECRET}" >> /var/www/html/.env
echo "PUSHER_CLUSTER=${PUSHER_CLUSTER}" >> /var/www/html/.env

echo "📋 Generated .env contents:"
cat /var/www/html/.env

# ── 1. Generate JWT keys if they don't exist ──────────────────────────────────
JWT_DIR=/var/www/html/config/jwt

if [ ! -f "$JWT_DIR/private.pem" ]; then
    echo "🔑 Generating JWT keys..."
    mkdir -p "$JWT_DIR"
    openssl genpkey \
        -algorithm RSA \
        -out "$JWT_DIR/private.pem" \
        -pkeyopt rsa_keygen_bits:4096 \
        -pass pass:"${JWT_PASSPHRASE}"
    openssl pkey \
        -in "$JWT_DIR/private.pem" \
        -out "$JWT_DIR/public.pem" \
        -pubout \
        -passin pass:"${JWT_PASSPHRASE}"
    chown -R www-data:www-data "$JWT_DIR"
    chmod 600 "$JWT_DIR/private.pem"
    chmod 644 "$JWT_DIR/public.pem"
    echo "✅ JWT keys generated."
else
    echo "✅ JWT keys already exist."
fi

# ── 2. Clear and warm up cache ────────────────────────────────────────────────
echo "🗂  Warming up cache..."
cd /var/www/html
rm -rf /var/www/html/var/cache/*
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
echo "✅ Cache ready."

# ── 3. Run database migrations ────────────────────────────────────────────────
echo "🗄  Running database migrations..."
php bin/console doctrine:migrations:sync-metadata-storage --env=prod || true
php bin/console doctrine:migrations:version --add --all --no-interaction --env=prod || true
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
echo "✅ Migrations complete."

# ── 4. Fix permissions ────────────────────────────────────────────────────────
chown -R www-data:www-data /var/www/html/var
chmod -R 777 /var/www/html/var

# ── 5. Configure Nginx port & start supervisor ────────────────────────────────
echo "🚀 Starting Nginx + PHP-FPM..."
echo "🔌 PORT is: ${PORT}"
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/conf.d/default.conf
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf