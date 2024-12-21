# Use the official PHP image with Alpine
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    libzip-dev \
    git \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the existing application directory contents
COPY . .

# Install PHP dependencies
RUN /usr/bin/composer install --ignore-platform-reqs --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts

# Set permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 9000
EXPOSE 80

# Start PHP-FPM server
CMD ["php-fpm"]
