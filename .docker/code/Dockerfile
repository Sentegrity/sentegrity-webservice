FROM ubuntu:14.04
MAINTAINER Ante Braovic <abraovic@gmail.com>

VOLUME ["/opt"]
WORKDIR /tmp

# install software needed to read and apply env vars
RUN apt-get update && apt-get install -y sed && rm -rf /var/lib/apt/lists/*

COPY etc/run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run

CMD ["/usr/local/bin/run"]