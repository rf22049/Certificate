# Dockerfile
FROM php:8.5-fpm-alpine

# Install system dependencies and necessary packages
RUN apk add --no-cache \
    nginx \
    git \
    unzip \
    libzip-dev \
    postgresql-dev \
    libpq-dev \
    nginx-common \
    && docker-php-ext-install pdo_mysql zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Nginx configuration
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 80

CMD ["php-fpm"]