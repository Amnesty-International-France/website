#!/bin/bash
set -e
set -x
echo $PATH
castor install --path=www

test -f ${APP_HOME}/user.ini && \
  cp ${APP_HOME}/user.ini ${APP_HOME}${CC_WEBROOT}/.user.ini

cp -f ${APP_HOME}/infogerance/aif-clever-cloud.php  ${APP_HOME}${CC_WEBROOT}/

# patch wp-config.php
if ! grep -q  aif-clever-cloud.php   www/wp-config.php  ; then
    sed -i "/Add any custom values between this line/a require ABSPATH . '/aif-clever-cloud.php';" www/wp-config.php
fi


# install restic for FS backup
RESTIC_TAG=0.17.3
curl -L https://github.com/restic/restic/releases/download/v${RESTIC_TAG}/restic_${RESTIC_TAG}_linux_amd64.bz2 | bunzip2 > /home/bas/.local/bin/restic
chmod +x /home/bas/.local/bin/restic

chmod +x ${APP_HOME}/infogerance/backup-wp.sh
