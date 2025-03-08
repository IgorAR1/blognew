FROM php:8.3-fpm

WORKDIR /var/www/blog

#RUN RUN groupadd -g 1001 igoryan
#RUN useradd -u 1001 -g igoryan:igoryan

RUN apt-get update \
  && apt-get install -y build-essential zlib1g-dev default-mysql-client curl gnupg procps vim git unzip libzip-dev libpq-dev \
  && docker-php-ext-install zip pdo_mysql pdo_pgsql pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
