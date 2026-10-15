#!/bin/sh
set -e

mkdir -p /var/www/html/var/cache /var/www/html/var/log

chown -R www-data:www-data /var/www/html/var

exec "$@"
