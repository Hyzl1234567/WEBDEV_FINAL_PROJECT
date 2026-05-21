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

# ── Stage 2: Node.js assets ───────────────────────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json* yarn.lock* ./
RUN npm install
COPY . .
RUN npm run build

# ── Stage 3: Production image ─────────────────────────────────────────────────
FROM php:8.3-fpm

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

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/custom.ini

COPY nginx.conf      /etc/nginx/nginx.conf
COPY nginx-main.conf /etc/nginx/conf.d/default.conf

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/var/cache \
    && mkdir -p /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]