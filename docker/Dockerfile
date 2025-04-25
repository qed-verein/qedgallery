FROM php:8.2.28-fpm

RUN apt update
RUN apt install libmagickwand-6.q16-6 libmagickcore-6.q16-6 libmagickwand-dev --yes
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN apt install jhead --yes
RUN docker-php-ext-install mysqli exif
RUN sh -c 'pear install mdb2 mdb2#mysqli || true'

