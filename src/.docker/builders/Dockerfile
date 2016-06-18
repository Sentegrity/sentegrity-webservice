##
#   This Dockerfile is used to build an image that can be used as base image for
#   NginX + PHP 7.0 + Symfony 2.8 support with Ubuntu 14.04 LTS OS.
#
#   It is not an intension to use this as standalone image. This image should be
#   used as base image for some other image.
#
#   @author: Ante Braovic <abraovic@gmail.com>
#
#   Installed:
#       - PHP 7.0
#       - NginX
#       - Sendmail
#
#   All configuration for nginx and fpm is added to work with symfony2.8 is added. When
#   new project is started be sure to add .docker folder into your project and edit
#   .docker/code/run.sh with needed ENV replacements
##
FROM ubuntu:14.04
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV WDIR sentegrity-webservice/src
ENV LDIR sentegrity-webservice/.local
ENV WDIRD ${WDIR}/.docker

## Install necessary software
RUN apt-get update && apt-get install -y \
    software-properties-common \
    language-pack-en-base \
    curl \
    supervisor \
    sendmail \
    sed \
    nginx \
&& rm -rf /var/lib/apt/lists/*

# install  php7
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
      php7.0-mysql \
      php7.0-imap \
      php7.0-mcrypt \
&& rm -rf /var/lib/apt/lists/*

## Configure NGINX
ADD ${WDIRD}/nginx/nginx.conf /etc/nginx/nginx.conf
ADD ${WDIRD}/nginx/symfony.conf /etc/nginx/sites-available/
RUN ln -s /etc/nginx/sites-available/symfony.conf /etc/nginx/sites-enabled/symfony
RUN rm -rf /etc/nginx/sites-enabled/default

## Configure FPM
COPY ${WDIRD}/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
COPY ${WDIRD}/php7.0-fpm/webservice/symfony.ini /etc/php/7.0/fpm/conf.d/
COPY ${WDIRD}/php7.0-fpm/webservice/symfony.ini /etc/php/7.0/cli/conf.d/
COPY ${WDIRD}/php7.0-fpm/webservice/symfony.pool.conf /etc/php/7.0/fpm/pool.d/

## Configure sendmail
RUN printf "sendmail_path = \"/usr/sbin/sendmail -t -i\"" >> /etc/php/7.0/fpm/php.ini;
RUN printf "sendmail_path = \"/usr/sbin/sendmail -t -i\"" >> /etc/php/7.0/cli/php.ini;

## SSL Pinning
RUN mkdir /etc/nginx/ssl
ADD ${LDIR}/certs/ssl/private/original.key /etc/nginx/ssl/nginx.key
ADD ${LDIR}/certs/ssl/certs/original.crt /etc/nginx/ssl/nginx.crt
ADD ${LDIR}/certs/ssl/private/backup.first.key /etc/nginx/ssl/nginx.backup.first.key
ADD ${LDIR}/certs/ssl/certs/backup.first.csr /etc/nginx/ssl/nginx.backup.first.csr
ADD ${LDIR}/certs/ssl/private/backup.second.key /etc/nginx/ssl/nginx.backup.second.key
ADD ${LDIR}/certs/ssl/certs/backup.second.csr /etc/nginx/ssl/nginx.backup.second.csr

## Env exporter
COPY ${WDIRD}/code/run.sh /usr/local/bin/run
RUN chmod +x /usr/local/bin/run