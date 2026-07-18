# syntax=docker/dockerfile:1

FROM php:8.4-fpm-bookworm

ARG INSTALL_XDEBUG=true
ARG NODE_MAJOR=22

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/var/www/.composer

# Instala dependências do sistema
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
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_pgsql \
    pgsql \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then \
    pecl install xdebug && docker-php-ext-enable xdebug; \
    fi \
    && apt-get purge -y --auto-remove gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configurações do PHP
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