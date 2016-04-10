## Start a project

### SSL Pinning - needs to be done before starting
In order to make ssl pinning you should do the following:

Copy your keys and certs to the following locations
```
.docker/nginx/etc/ssl/private/original.key
.docker/nginx/etc/ssl/certs/original.crt
.docker/nginx/etc/ssl/private/backup.first.key
.docker/nginx/etc/ssl/certs/backup.first.csr
.docker/nginx/etc/ssl/private/backup.second.key
.docker/nginx/etc/ssl/certs/backup.second.csr
```
After `sudo docker-compose up -d` run `sudo docker exec -it s_loadbalancer /bin/bash` and edit `/etc/nginx/sites-available/symfony.conf`. Add pins from host machine that are located at `/etc/ssl/nginx/certs.fingerprint` to:
```
add_header Public-Key-Pins 'pin-sha256="-->orig<--"; pin-sha256="-->first bck<--"; pin-sha256="-->second bck<--"; max-age=10';
```
**if file `/etc/ssl/nginx/certs.fingerprint` does not exist or is outdated you can generate new finger prints:*
```
openssl req -pubkey < {your cert file here} | openssl pkey -pubin -outform der | openssl dgst -sha256 -binary | base64
```
Reload nginx
```
service nginx reload
```
NOTE: **vim** editor is installed. If you with any other you can run:

```
apt-get update && apt-get install -y {your editor package name}
```

### Start an app

Pull the stuff from git repo then enter the directory and run:

```
cd src/symfony/
composer install
cd ../../
sudo docker-compose -f .docker/docker-image-builder.yaml build
sudo docker-compose up -d
```

For this to work you should install [docker] and [composer] on your machine.

[composer]: http://getcomposer.org/
[docker]: https://docs.docker.com/engine/installation/