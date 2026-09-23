FROM php:8.2-apache

RUN apt-get update && apt-get install -y libicu-dev libzip-dev unzip git && docker-php-ext-install intl pdo_mysql opcache && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist

COPY . .

COPY apache.conf /etc/apache2/conf-available/symfony.conf
RUN a2enconf symfony

RUN mkdir -p var/cache var/log var/share && chown -R www-data:www-data var

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
