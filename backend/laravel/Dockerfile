FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql bcmath

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

# Configure Apache to use server.php as the index if public/index.php is missing
RUN echo "DirectoryIndex server.php index.php index.html" >> /etc/apache2/apache2.conf

# Allow .htaccess to override configuration
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

USER www-data

EXPOSE 80

CMD ["apache2-foreground"]
