FROM sentegrity/base:latest
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV WDIR sentegrity-webservice/src
ENV WDIRD ${WDIR}/.docker

RUN usermod -u 1000 www-data
## xdebug stuff
RUN apt-get update && apt-get install -y snmp php7.0-dev php-pear && rm -rf /var/lib/apt/lists/*; fi
RUN pecl install xdebug && printf "\nzend_extension=\"/usr/lib/php/20151012/xdebug.so\"\nxdebug.remote_enable=1\nxdebug.remote_connect_back=1\n" >> /etc/php/7.0/fpm/php.ini; fi

## Add source
COPY ${WDIR}/symfony /opt/symfony
COPY ${WDIR}/web /var/www

## Add cronjob
ADD ${WDIRD}/php7.0-fpm/batchjobs/crontab /etc/cron.d/batch-jobs-cron
RUN chmod 0644 /etc/cron.d/batch-jobs-cron
RUN touch /var/log/cron.log

CMD cron && tail -f /var/log/cron.log