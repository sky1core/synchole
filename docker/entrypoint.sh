#!/usr/bin/env bash

set -x

crontab /app/app_cron


mkdir -p /app/data/workspace
mkdir -p /app/data/database
touch /app/data/database/database.sqlite

sudo chown synchole:www-data /app/data
sudo chown synchole:www-data /app/data/database/database.sqlite
sudo chown synchole:www-data /app/data/database
sudo chmod 775 /app/data/database
sudo chmod 664 /app/data/database/database.sqlite
php artisan migrate --force


### run supervisor
sudo supervisord -n -c "/etc/supervisor/supervisord.conf"

set +x
