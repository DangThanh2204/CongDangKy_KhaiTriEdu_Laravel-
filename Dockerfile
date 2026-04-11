FROM node:20-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

FROM php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        git \
        unzip \
        libicu-dev \
        libjpeg62-turbo-dev \
        libssl-dev \
        libpng-dev \
        libfreetype6-dev \
        libsqlite3-dev \
        libzip-dev \
        pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && pecl install mongodb \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mysqli \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . .

RUN composer install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction \
        --no-progress

COPY --from=node-build /app/public/build /var/www/html/public/build

RUN chmod +x /var/www/html/scripts/render-start.sh \
    && mkdir -p /var/www/html/storage/app /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000

CMD ["/var/www/html/scripts/render-start.sh"]
