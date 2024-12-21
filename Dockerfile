FROM php:8.2-fpm

# Install PostgreSQL extension
# TODO: move this part to the base image
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    supervisor \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache

# Copy composer.lock and composer.json
COPY composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

RUN <<EOF
curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer
EOF

COPY --chown=www:www . /var/www

RUN /usr/local/bin/composer install --ignore-platform-reqs --prefer-dist --no-ansi --no-interaction --no-progress --no-scripts

RUN php artisan cache:clear \
&& php artisan config:clear \
&& php artisan route:clear \
&& php artisan view:clear

# Link Laravel storage
RUN ln -s /var/www/storage/app/public /var/www/public/storage