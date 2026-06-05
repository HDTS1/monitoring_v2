FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libssl-dev \
    zlib1g-dev \
    libzip-dev \
    libonig-dev \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    intl \
    zip \
    opcache

# Install memcache (the older non-igbinary version, matching what the app uses)
RUN pecl install memcache-8.2 \
    && docker-php-ext-enable memcache

# Enable Apache modules
RUN a2enmod rewrite headers remoteip setenvif

# PHP configuration
RUN echo "date.timezone = Europe/Bratislava" >> /usr/local/etc/php/conf.d/timezone.ini
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=10000\nopcache.revalidate_freq=60" \
    >> /usr/local/etc/php/conf.d/opcache.ini

# Apache configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Copy application code
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 80
