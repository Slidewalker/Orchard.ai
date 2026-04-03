FROM php:8.2-fpm
WORKDIR /var/www
COPY . .
RUN docker-php-ext-install pdo_mysql
CMD ["php-fpm"]
