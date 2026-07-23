# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm

ARG INSTALL_XDEBUG=true
ARG NODE_MAJOR=22

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/var/www/.composer

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    unzip \
    zip \
    gnupg \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    libexif-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get update && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_pgsql \
    pgsql \
    zip

# Install PECL Redis with timeout and retry logic
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && pecl channel-update pecl.php.net || true \
    && (pecl install redis-5.3.7 || pecl install redis) \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove build-essential \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Xdebug if needed
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
    apt-get update && apt-get install -y --no-install-recommends build-essential && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apt-get purge -y --auto-remove build-essential && \
    apt-get clean && rm -rf /var/lib/apt/lists/*; \
    fi

# Cleanup gnupg
RUN apt-get purge -y --auto-remove gnupg || true

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP configurations
COPY docker/php/conf.d/laravel.ini /usr/local/etc/php/conf.d/laravel.ini
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/conf.d/xdebug.ini /tmp/xdebug.ini

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www/.composer \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then \
    mv /tmp/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini; \
    else \
    rm -f /tmp/xdebug.ini; \
    fi

WORKDIR /var/www/html

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php-fpm -t || exit 1

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
