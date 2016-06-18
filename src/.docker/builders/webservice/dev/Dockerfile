FROM sentegrity/base:latest
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive

RUN usermod -u 1000 www-data
## xdebug stuff
RUN apt-get update && apt-get install -y snmp php7.0-dev php-pear && rm -rf /var/lib/apt/lists/*; fi
RUN pecl install xdebug && printf "\nzend_extension=\"/usr/lib/php/20151012/xdebug.so\"\nxdebug.remote_enable=1\nxdebug.remote_connect_back=1\n" >> /etc/php/7.0/fpm/php.ini; fi

## Expose ports and add command
EXPOSE 443

CMD ["/usr/bin/supervisord"]