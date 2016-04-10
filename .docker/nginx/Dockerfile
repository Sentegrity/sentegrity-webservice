FROM ubuntu:14.04
MAINTAINER Ante Braovic <abraovic@gmail.com>


RUN apt-get update && apt-get install -y nginx vim && rm -rf /var/lib/apt/lists/*

ADD etc/nginx.conf /etc/nginx/nginx.conf
ADD etc/symfony.conf /etc/nginx/sites-available/
RUN ln -s /etc/nginx/sites-available/symfony.conf /etc/nginx/sites-enabled/symfony
RUN rm -rf /etc/nginx/sites-enabled/default
ADD etc/upstream.conf /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

# add certificates (make sure those are stored on a secure place)
RUN mkdir /etc/nginx/ssl
ADD etc/ssl/private/original.key /etc/nginx/ssl/nginx.key
ADD etc/ssl/certs/original.crt /etc/nginx/ssl/nginx.crt
ADD etc/ssl/private/backup.first.key /etc/nginx/ssl/nginx.backup.first.key
ADD etc/ssl/certs/backup.first.csr /etc/nginx/ssl/nginx.backup.first.csr
ADD etc/ssl/private/backup.second.key /etc/nginx/ssl/nginx.backup.second.key
ADD etc/ssl/certs/backup.second.csr /etc/nginx/ssl/nginx.backup.second.csr

CMD ["nginx"]

EXPOSE 443