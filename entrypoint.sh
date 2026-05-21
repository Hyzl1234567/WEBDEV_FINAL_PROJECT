#!/bin/sh
set -e

echo "🌿 EcoBrew — Starting up..."

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
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod
echo "✅ Cache ready."

# ── 3. Run database migrations ────────────────────────────────────────────────
echo "🗄  Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
echo "✅ Migrations complete."

# ── 4. Fix permissions ────────────────────────────────────────────────────────
chown -R www-data:www-data /var/www/html/var
chmod -R 777 /var/www/html/var

# ── 5. Start supervisor (manages both Nginx + PHP-FPM) ───────────────────────
echo "🚀 Starting Nginx + PHP-FPM..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf