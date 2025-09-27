ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions intl apcu zip pcov

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install

COPY php.ini /usr/local/etc/php/conf.d/app.ini
COPY . .
# CMD vendor/bin/phpunit

