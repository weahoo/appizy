FROM php:5.6-cli

WORKDIR /usr/src/myapp

RUN apt-get update \
    && apt-get -y install libtidy-dev \
    && docker-php-ext-install tidy \

    && apt-get -y install zlib1g-dev \
    && docker-php-ext-install zip \
