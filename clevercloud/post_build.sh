#!/bin/bash
set -e
echo $PATH
if [ -n "$APP_PLUGIN_TOKEN" ]; then castor install --token "$APP_PLUGIN_TOKEN" --path=www; else castor install --path=www; fi

test -f ${APP_HOME}/user.ini && \
  cp ${APP_HOME}/user.ini ${APP_HOME}${CC_WEBROOT}/.user.ini

cp -f ${APP_HOME}/infogerance/aif-clever-cloud.php  ${APP_HOME}${CC_WEBROOT}/wp-config.php

# rysnc plugins if exists on repo
if test -d ${APP_HOME}/wp-content/plugins  ; then
    rsync -a ${APP_HOME}/wp-content/plugins/ ${APP_HOME}${CC_WEBROOT}/wp-content/plugins/
fi

# rysnc plugins if exists on repo
if test -d ${APP_HOME}/wp-content/themes  ; then
    rsync -a  --delete ${APP_HOME}/wp-content/themes/ ${APP_HOME}${CC_WEBROOT}/wp-content/themes/
fi

# install restic for FS backup
RESTIC_TAG=0.17.3
curl -L https://github.com/restic/restic/releases/download/v${RESTIC_TAG}/restic_${RESTIC_TAG}_linux_amd64.bz2 | bunzip2 > /home/bas/.local/bin/restic
chmod +x /home/bas/.local/bin/restic

chmod +x ${APP_HOME}/infogerance/backup-wp.sh
