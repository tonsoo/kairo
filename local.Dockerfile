# syntax=docker/dockerfile:1.4
FROM dunglas/frankenphp:php8.4

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions \
    @composer \
    intl \
    pdo_mysql \
    mysqli \
    zip \
    bcmath \
    exif \
    gd \
    opcache

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt update && apt install -y nodejs poppler-utils && \
    rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/app.ini
