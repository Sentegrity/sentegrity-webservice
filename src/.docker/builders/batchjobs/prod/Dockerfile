FROM sentegrity/base:latest
MAINTAINER Ante Braovic <abraovic@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV WDIR sentegrity-webservice/src
ENV WDIRD ${WDIR}/.docker

## Add source
COPY ${WDIR}/symfony /opt/symfony
COPY ${WDIR}/web /var/www

## Add cronjob
ADD ${WDIRD}/php7.0-fpm/batchjobs/crontab /etc/cron.d/batch-jobs-cron
RUN chmod 0644 /etc/cron.d/batch-jobs-cron
RUN touch /var/log/cron.log

CMD cron && tail -f /var/log/cron.log