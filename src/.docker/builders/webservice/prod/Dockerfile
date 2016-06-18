FROM sentegrity/base:latest
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV WDIR sentegrity-webservice/src

## Add source
COPY ${WDIR}/symfony /opt/symfony
COPY ${WDIR}/web /var/www

## Expose ports and add command
EXPOSE 443

CMD ["/usr/bin/supervisord"]