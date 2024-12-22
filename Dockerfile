# Use the official PHP image with the required extensions
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    nano \
    sqlite3 libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory permissions
COPY . .

RUN git config --global --add safe.directory /var/www/html

# Install Laravel dependencies
RUN /usr/bin/composer install --ignore-platform-reqs --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts
RUN cp /var/www/html/.env.example .env
RUN php artisan key:generate
RUN php artisan migrate --seed
# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R :www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage
# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
