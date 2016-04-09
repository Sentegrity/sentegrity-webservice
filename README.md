## Start a project

Pull the stuff from git repo then enter the directory and run:

```
cd src/symfony/
composer install
cd ../../
docker-compose -f .docker/docker-image-builder.yaml build
docker-compose up -d
```

For this to work you should install [docker] and [composer] on your machine.

[composer]: http://getcomposer.org/
[docker]: https://docs.docker.com/engine/installation/