FROM php:8.1-apache

# Cài tiện ích
RUN apt-get update && apt-get install -y \
    libpng-dev zip unzip curl git libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Thay DocumentRoot của Apache sang thư mục public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Bật mod_rewrite
RUN a2enmod rewrite

# Cấp quyền cho storage và bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
