#!/usr/bin/env bash

mkdir -p ./var/cache ./var/log

composer install

sqlite3 ./var/data.db < ./infra/sqlite/schema.sql

chown -R www-data:www-data ./var ./vendor composer.lock symfony.lock

exec "$@"
