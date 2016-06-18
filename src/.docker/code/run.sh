#!/bin/bash
## clear caches and fix permissions
rm -rf /opt/symfony/app/cache/*
rm -rf /opt/symfony/app/logs/*
chown -R www-data:www-data /opt/symfony/app/cache/
chown -R www-data:www-data /opt/symfony/app/logs/