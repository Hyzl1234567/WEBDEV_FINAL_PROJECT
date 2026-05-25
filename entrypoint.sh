#!/bin/sh
set -e

echo "🌿 EcoBrew — Starting up..."

# ── 0. Create .env file from Railway environment variables ────────────────────
echo "APP_ENV=prod" > /var/www/html/.env
echo "APP_SECRET=${APP_SECRET}" >> /var/www/html/.env
echo "DATABASE_URL=${DATABASE_URL}" >> /var/www/html/.env
echo "JWT_PASSPHRASE=${JWT_PASSPHRASE}" >> /var/www/html/.env
echo "JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem" >> /var/www/html/.env
echo "JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem" >> /var/www/html/.env
echo "MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN}" >> /var/www/html/.env
echo "MAILER_DSN=${MAILER_DSN}" >> /var/www/html/.env
echo "CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-'^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'}" >> /var/www/html/.env
echo "GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}" >> /var/www/html/.env
echo "GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}" >> /var/www/html/.env
echo "PUSHER_APP_ID=${PUSHER_APP_ID}" >> /var/www/html/.env
echo "PUSHER_KEY=${PUSHER_KEY}" >> /var/www/html/.env
echo "PUSHER_SECRET=${PUSHER_SECRET}" >> /var/www/html/.env
echo "PUSHER_CLUSTER=${PUSHER_CLUSTER}" >> /var/www/html/.env

echo "📋 Generated .env contents:"
cat /var/www/html/.env

# ── 1a. Write Firebase service account credentials ────────────────────────────
FIREBASE_DIR=/var/www/html/config/firebase
mkdir -p "$FIREBASE_DIR"
if [ -n "${FIREBASE_CREDENTIALS_BASE64}" ]; then
    echo "${FIREBASE_CREDENTIALS_BASE64}" | base64 -d > "$FIREBASE_DIR/serviceAccountKey.json"
    echo "✅ Firebase credentials written."
elif [ -n "${FIREBASE_CREDENTIALS}" ]; then
    echo "${FIREBASE_CREDENTIALS}" > "$FIREBASE_DIR/serviceAccountKey.json"
    echo "✅ Firebase credentials written (raw JSON)."
else
    echo "⚠️  No FIREBASE_CREDENTIALS set — Google mobile login will be unavailable."
fi

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

# Only skip migrations on very first boot (no recorded executions yet).
# On subsequent boots, run migrate normally so new migrations actually execute.
EXECUTED=$(php bin/console doctrine:migrations:list --env=prod --no-interaction 2>/dev/null | grep -c "migrated" || echo "0")
if [ "$EXECUTED" = "0" ]; then
    echo "⚙️  First boot — marking existing migrations as done (tables already exist)..."
    php bin/console doctrine:migrations:version --add --all --no-interaction --env=prod || true
fi

php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true
echo "✅ Migrations complete."

# ── 3b. Ensure sessions table exists (required for PDO session handler) ───────
echo "🗄  Ensuring sessions table exists..."
php bin/console dbal:run-sql "CREATE TABLE IF NOT EXISTS sessions (sess_id VARCHAR(128) NOT NULL PRIMARY KEY, sess_data MEDIUMBLOB NOT NULL, sess_time INTEGER UNSIGNED NOT NULL, sess_lifetime INTEGER UNSIGNED NOT NULL) COLLATE utf8mb4_bin ENGINE=InnoDB" --env=prod || true
echo "✅ Sessions table ready."

# ── 4. Fix permissions ────────────────────────────────────────────────────────
chown -R www-data:www-data /var/www/html/var
chmod -R 777 /var/www/html/var

# ── 5. Configure Nginx port & start supervisor ────────────────────────────────
echo "🚀 Starting Nginx + PHP-FPM..."
echo "🔌 PORT is: ${PORT}"
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/conf.d/default.conf
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf