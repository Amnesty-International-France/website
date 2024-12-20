#!/bin/bash -l

if [[ "$INSTANCE_NUMBER" != "0" ]]; then
     echo "Instance number is ${INSTANCE_NUMBER}. Stop here."
     exit 0
fi

export PATH=/home/bas/.local/bin:$PATH
export AWS_ACCESS_KEY_ID=${CELLAR_ADDON_KEY_ID}
export AWS_SECRET_ACCESS_KEY=${CELLAR_ADDON_KEY_SECRET}

export RESTIC_REPOSITORY=s3:https://${CELLAR_ADDON_HOST}/${BUCKET_NAME}
export DOCROOT=${APP_HOME}${CC_WEBROOT}/
restic backup ${DOCROOT}

# on garde uniquement les 30 derniers backups
restic forget --keep-last 30
