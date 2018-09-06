FROM php:5.6-fpm

RUN docker-php-ext-install pdo_mysql mbstring bcmath
RUN pecl install sundown-0.3.11 && docker-php-ext-enable sundown

RUN echo "date.timezone = Europe/Paris" > /usr/local/etc/php/php.ini

WORKDIR /usr/src/app

COPY . /usr/src/app

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN rm -rf /usr/src/app/app/cache/*

RUN composer install