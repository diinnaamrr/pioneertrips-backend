FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first (for better caching)
COPY composer.json ./
COPY composer.lock* ./

# Set memory limit for composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Install PHP dependencies WITHOUT scripts (scripts need app files)
# Using --no-scripts to skip post-autoload-dump which requires app files
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --ignore-platform-reqs || \
    (echo "Retrying composer install..." && \
     composer clear-cache && \
     composer install --no-dev --no-scripts --no-interaction --prefer-dist --ignore-platform-reqs)

# Copy existing application directory contents
COPY . /var/www/html

# Now run composer scripts and optimize autoloader (all files are present)
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]


