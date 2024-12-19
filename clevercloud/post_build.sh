#!/bin/bash
set -x
set -e
echo $PATH
castor install --path=www

test -f ${APP_HOME}/user.ini && \
  cp ${APP_HOME}/user.ini ${APP_HOME}${CC_WEBROOT}/.user.ini

# patch wp-config.php for the WP CORE
if ! grep -q  WP_AUTO_UPDATE_CORE  www/wp-config.php  ; then
    sed -i "/Add any custom values between this line/a define( 'WP_AUTO_UPDATE_CORE', true);" www/wp-config.php
fi


# install restic for FS backup
RESTIC_TAG=0.17.3
curl -L https://github.com/restic/restic/releases/download/v${RESTIC_TAG}/restic_${RESTIC_TAG}_linux_amd64.bz2 | bunzip2 > /home/bas/.local/bin/restic
chmod +x /home/bas/.local/bin/restic

chmod +x ${APP_HOME}/infogerance/backup-wp.sh
