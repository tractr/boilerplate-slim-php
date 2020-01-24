FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y libmcrypt-dev mysql-client --no-install-recommends && \
    docker-php-ext-install mcrypt pdo_mysql
