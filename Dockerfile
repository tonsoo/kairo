FROM dunglas/frankenphp:php8.4-alpine AS base

WORKDIR /app

COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions \
    intl pdo_mysql pgsql zip bcmath exif gd opcache pcntl sockets \
    && rm -rf /var/cache/apk/* /tmp/*

FROM dunglas/frankenphp:php8.4-alpine AS builder

WORKDIR /app

RUN apk add --no-cache nodejs npm git unzip

RUN --mount=type=bind,from=mlocati/php-extension-installer:latest,source=/usr/bin/install-php-extensions,target=/usr/local/bin/install-php-extensions \
    install-php-extensions @composer intl zip dom fileinfo tokenizer session

# Copy dependency manifests first
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Install PHP deps with cache
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
      --no-interaction \
      --no-scripts \
      --no-dev \
      --prefer-dist \
      --optimize-autoloader

# Install JS deps with cache
RUN --mount=type=cache,target=/root/.npm \
    npm ci

# Copy the rest of the application only now
COPY . .

RUN touch .dockerenv

# Build frontend
RUN npm run build && rm -rf node_modules

FROM infisical/cli:0.43.99 AS infisical-cli

FROM base AS setup

WORKDIR /app

COPY --from=builder /app /app

RUN mkdir -p \
    /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache \
 && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public

COPY --from=infisical-cli /bin/infisical /usr/local/bin/infisical

RUN chmod +x /usr/local/bin/infisical && infisical --version
