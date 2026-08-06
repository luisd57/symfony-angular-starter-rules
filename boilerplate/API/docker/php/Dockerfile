FROM php:8.4-fpm

# System dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    unzip \
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

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

# vendor/ and var/ MUST exist in the image before the chown. Docker initialises a
# named volume whose path is absent from the image as root:root, and the non-root
# user then cannot write to it — composer install fails with
# "vendor/symfony does not exist and could not be created".
RUN mkdir -p /var/www/html/vendor /var/www/html/var \
    && useradd -ms /bin/bash symfony \
    && chown -R symfony:symfony /var/www/html

# Run PHP-FPM as the non-root user so files it writes are owned by symfony.
RUN sed -i 's/www-data/symfony/g' /usr/local/etc/php-fpm.d/www.conf

USER symfony

EXPOSE 9000

CMD ["php-fpm"]
