FROM php:7.4-fpm

RUN apt-get update
RUN apt-get install -y libmcrypt-dev mariadb-client --no-install-recommends
RUN docker-php-ext-install mysqli pdo pdo_mysql opcache

RUN apt-get clean -y && \
    apt-get autoclean -y && \
    apt-get autoremove -y

