# Use the official PHP image with the required extensions
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

COPY composer.json /var/www/html

# Set working directory
WORKDIR /var/www/html

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Install Composer
RUN <<EOF
curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
EOF

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Install Laravel dependencies
RUN /usr/bin/composer install --ignore-platform-reqs --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
