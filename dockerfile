# Use the official PHP image
FROM php:8.1-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring zip pdo pdo_mysql

# Set working directory
WORKDIR /var/www

# Copy the composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install project dependencies
RUN composer update

# Copy the rest of the application code
COPY . /var/www

# Expose port 3030
EXPOSE 3030

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:3030", "-t", "public"]
