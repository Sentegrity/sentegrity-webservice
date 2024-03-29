#!/usr/bin/env bash

if [ "$1" != "prod" ]; then
    if [ "$1" != "dev" ]; then
        echo "You should pass either prod or dev"
        exit 1
    fi
fi

ENV=$1

sudo rm -rf src/symfony/app/cache/*
sudo rm -rf src/symfony/app/logs/*

## make composer install
echo "Install dependencies\n"
cd src/symfony
composer install

cd ../../

## clear caches and logs
echo "Clear caches and logs\n"
sudo rm -rf src/symfony/app/cache/*
sudo rm -rf src/symfony/app/logs/*

## make docker base image
echo "Make base image\n"
docker build -t sentegrity/base -f src/.docker/builders/Dockerfile ../
## make app image
docker build -t sentegrity/web-service -f src/.docker/builders/webservice/$ENV/Dockerfile ../
docker build -t sentegrity/batch-jobs -f src/.docker/builders/batchjobs/$ENV/Dockerfile ../

## create database schema if dev
if [ "$1" == "dev" ]; then
    docker-compose up -d
    cd src/symfony
    app/console doctrine:schema:update --force
fi

exit 0
