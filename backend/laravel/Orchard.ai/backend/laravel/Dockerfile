FROM php:8.1-fpm

RUN apt-get update \
  && apt-get install -y git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY php-entrypoint.sh /usr/local/bin/php-entrypoint
RUN chmod +x /usr/local/bin/php-entrypoint

ENTRYPOINT ["php-entrypoint"]
CMD ["php-fpm"]
