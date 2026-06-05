FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libssl-dev \
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
    zip

# Install memcache
RUN pecl install memcache-8.2 \
    && docker-php-ext-enable memcache

# PHP timezone
RUN echo "date.timezone = Europe/Bratislava" >> /usr/local/etc/php/conf.d/timezone.ini

# Copy app code
COPY . /app/

# Copy crontab
COPY docker/cron/crontab /etc/cron.d/hdts-cron

# Apply crontab permissions
RUN chmod 0644 /etc/cron.d/hdts-cron \
    && crontab /etc/cron.d/hdts-cron

# Create log file
RUN touch /var/log/cron.log

WORKDIR /app

CMD ["cron", "-f"]
