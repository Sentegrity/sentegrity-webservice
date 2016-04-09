FROM ubuntu:14.04
MAINTAINER Ante Braovic <abraovic@gmail.com>

ARG DEV

RUN apt-get update && apt-get install -y \
	supervisor \
	software-properties-common \
    language-pack-en-base \
&& rm -rf /var/lib/apt/lists/*

# add php7 repo
RUN LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php

RUN apt-get update && apt-get install -y php7.0 \
      php7.0-cli \
      php7.0-gd \
      php7.0-json \
      php7.0-cgi \
      php7.0-curl \
      php7.0-fpm \
      php7.0-mbstring \
      php7.0-xml \
      php7.0-zip \
      php7.0-imap \
      php7.0-mcrypt \
&& rm -rf /var/lib/apt/lists/*

COPY etc/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
COPY etc/symfony.ini /etc/php/7.0/fpm/conf.d/
COPY etc/symfony.ini /etc/php/7.0/cli/conf.d/
COPY etc/symfony.pool.conf /etc/php/7.0/fpm/pool.d/
RUN usermod -u 1000 www-data

## xdebug stuff
RUN if env | grep -q DEV=1; then apt-get update && apt-get install -y snmp php7.0-dev php-pear && rm -rf /var/lib/apt/lists/*; fi
RUN if env | grep -q DEV=1; then pecl install xdebug && printf "zend_extension=\"/usr/lib/php/20151012/xdebug.so\"\nxdebug.remote_enable=1\nxdebug.remote_connect_back=1\n" >> /etc/php/7.0/fpm/php.ini; fi

EXPOSE 9000
CMD ["/usr/bin/supervisord"]