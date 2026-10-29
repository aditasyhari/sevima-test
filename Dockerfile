# Stage 1: Build Frontend Assets (Vue 3 + Vite)
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Production PHP-FPM + Nginx Environment (Secure, Patched)
FROM php:8.3-fpm-alpine AS app

# Install system dependencies & PostgreSQL development libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    netcat-openbsd \
    postgresql-dev \
    libpq-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    opcache \
    zip \
    intl \
    mbstring

# Secure PHP configuration (Anti-RCE & Hardening)
RUN echo "expose_php = Off" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/security.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Copy built frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build /var/www/public/build

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN tr -d '\r' < /usr/local/bin/entrypoint.sh > /usr/local/bin/entrypoint.sh.tmp \
    && mv /usr/local/bin/entrypoint.sh.tmp /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Install production PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

