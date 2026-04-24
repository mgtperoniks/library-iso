# STAGE 1: PHP Application
FROM php:8.2-fpm-alpine

# Arguments defined in docker-compose.yml
ARG user=laravel
ARG uid=1000

# Install system dependencies & PHP extensions
# Menggunakan Alpine agar image jauh lebih kecil dan build lebih cepat
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN adduser -D -u $uid -h /home/$user $user
RUN mkdir -p /var/www && chown -R $user:$user /var/www

# Set working directory
WORKDIR /var/www

# Copy application files (termasuk folder public yang berisi aset)
COPY --chown=$user:$user . .

USER $user

EXPOSE 9000
CMD ["php-fpm"]
