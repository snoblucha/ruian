FROM php:7.4-apache
RUN apt-get update && apt-get install -y \
        mariadb-client\
        libzip-dev\
    && docker-php-ext-install -j$(nproc) iconv mysqli\
    && docker-php-ext-install -j$(nproc) pdo_mysql\
    && docker-php-ext-install -j$(nproc) zip

RUN a2enmod rewrite

CMD /usr/sbin/apache2ctl -D FOREGROUND
