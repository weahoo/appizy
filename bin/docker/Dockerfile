FROM php:5.6-cli

WORKDIR /usr/src/myapp

RUN apt-get update \
    && apt-get -y install libtidy-dev \
    && docker-php-ext-install tidy \

    && apt-get -y install zlib1g-dev \
    && docker-php-ext-install zip

# zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so
RUN pecl install xdebug
RUN echo 'zend_extension = /usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so' >> /usr/local/etc/php/php.ini
RUN touch /usr/local/etc/php/conf.d/xdebug.ini; \
    echo xdebug.remote_enable=1 >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo xdebug.remote_autostart=0 >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo xdebug.remote_connect_back=1 >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo xdebug.remote_port=9000 >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo xdebug.remote_log=/tmp/php5-xdebug.log >> /usr/local/etc/php/conf.d/xdebug.ini;
