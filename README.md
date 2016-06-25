# Start a project

Make sure you have all right permissions to clone repo.

## Get project from the repo

```sh
# Assuming you have Git installed and on your PATH.
git clone git@github.com:Sentegrity/sentegrity-webservice.git
cd sentegrity-webservice/
git fetch && git checkout your-branch
```

## First time build

When building this project for the first time there are several thing that needs to be done once in order for everything to work as it should. Most of the job is related to generating and storing certificates and it's fingerprints in order to make SSL Pinning work.

### SSL Pinning
In order to make ssl pinning you should do the following:

Copy your keys and certs to the following locations
```
.local/certs/ssl/private/original.key
.local/certs/ssl/certs/original.crt

.local/certs/ssl/private/backup.first.key
.local/certs/ssl/certs/backup.first.csr

.local/certs/ssl/private/backup.second.key
.local/certs/ssl/certs/backup.second.csr
```

For each certificate you should generate fingerprint using
```
openssl req -pubkey < {your cert file here} | openssl pkey -pubin -outform der | openssl dgst -sha256 -binary | base64
```

And then sore them for future use in `.local/certs/certs.fingerprint`.

At the end you should update `src/.docker/nginx/symfony.conf` with new fingerprints by replacing an existing fingerprints in the line:
```
add_header Public-Key-Pins 'pin-sha256="-->orig<--"; pin-sha256="-->first bck<--"; pin-sha256="-->second bck<--"; max-age=10';
```


### Create local environment files

#### Parameter environment file
Create file environment.yml in `.local/` and replace `&string&` with proper data
```
# MySQL credential data
database_host:          &database_host&
database_port:          &database_port&
database_name:          &database_name&
database_user:          &database_user&
database_password:      &database_password&

# Mail data
mailer_transport:  &mailer_transport&
mailer_host:       &mailer_host&
mailer_user:       &mailer_user&
mailer_password:   &mailer_password&

# A secret key that's used to generate certain security-related tokens
secret: &secret&
```

#### Docker compose environment file
Create file docker.env.yml in `.local/` and replace `&string&` with proper data
```sh
volumes_main: &volumes_main&

ports:
    https: '&https&'
    mysql: '&mysql&'
```

## Each time build
You need to start [Docker] and build the project. This docker is using a [VirtualBox]. Please download and install both of them to use it.

```sh
# Assuming you have Docker and VirtualBox installed and Composer installed on your PATH
./build.sh [prod | dev]

# prod -> prepares a project for production
# dev -> prepares a project for development
```

#### If docker is not updated you can run several command that will reduce build time:
```sh
# Assuming you have Docker and VirtualBox installed and Composer installed on your PATH
cd src/symfony/
composer install

## if docker containers are down run
cd ../../
docker-compose up -d
cd src/symfony
app/console doctrine:schema:update --force

## to check if containers are running run
docker-compose ps
```

Push stuff on your branch. DO NOT PUSH stuff on MASTER.

[composer]:https://getcomposer.org/download/
[Docker]:https://docs.docker.com/
[VirtualBox]:https://www.virtualbox.org/wiki/Downloads