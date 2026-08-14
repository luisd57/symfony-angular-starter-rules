# Same base and extensions as the php service, plus cron itself.
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    cron \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Same named-volume ownership trap as the php image - see docker/php/Dockerfile.
RUN mkdir -p /var/www/html/vendor /var/www/html/var \
    && useradd -ms /bin/bash symfony \
    && chown -R symfony:symfony /var/www/html

COPY docker/cron/crontab /tmp/crontab
RUN crontab -u symfony /tmp/crontab && rm /tmp/crontab

# cron itself runs as root to manage user crontabs; the jobs run as symfony.
CMD ["cron", "-f"]
