#!/usr/bin/env bash

set -x

crontab /app/app_cron


mkdir -p /etc/synchole/data/workspace
mkdir -p /etc/synchole/data/database
mkdir -p /etc/synchole/portainer
touch /etc/synchole/data/database/database.sqlite

sudo chown synchole:www-data /etc/synchole/data
sudo chown synchole:www-data /etc/synchole/data/database/database.sqlite
sudo chown synchole:www-data /etc/synchole/data/database
sudo chmod 775 /etc/synchole/data/database
sudo chmod 664 /etc/synchole/data/database/database.sqlite
php artisan migrate --force


### run supervisor
sudo supervisord -n -c "/etc/supervisor/supervisord.conf"

set +x
