FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
# ARG user
# ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd


# Set working directory
WORKDIR /var/www

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --chown=www:www . /var/www

RUN /usr/bin/composer install --ignore-platform-reqs --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts
