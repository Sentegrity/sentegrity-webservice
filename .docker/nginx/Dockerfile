FROM ubuntu:14.04
MAINTAINER Ante Braovic <abraovic@gmail.com>


RUN apt-get update && apt-get install -y nginx && rm -rf /var/lib/apt/lists/*

ADD etc/nginx.conf /etc/nginx/nginx.conf
ADD etc/symfony.conf /etc/nginx/sites-available/
RUN ln -s /etc/nginx/sites-available/symfony.conf /etc/nginx/sites-enabled/symfony
RUN rm -rf /etc/nginx/sites-enabled/default
ADD etc/upstream.conf /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data

CMD ["nginx"]

EXPOSE 80
EXPOSE 443