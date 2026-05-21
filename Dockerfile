# ── Stage 1: PHP dependencies ─────────────────────────────────────────────────
FROM composer:2.7 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ── Stage 2: Production image ─────────────────────────────────────────────────
FROM php:8.3-fpm

# Install system dependencies + Nginx + Supervisor
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        intl \
        gd \
        zip \
        opcache \
        mbstring \
        xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /etc/supervisor/conf.d \
    && mkdir -p /var/log/supervisor

# Copy PHP config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/custom.ini

# Copy Nginx config (after nginx is installed)
COPY nginx.conf      /etc/nginx/nginx.conf
COPY nginx-main.conf /etc/nginx/conf.d/default.conf

# Copy supervisor config (after supervisor is installed)
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy app
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/var/cache \
    && mkdir -p /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var

# Copy and set entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]