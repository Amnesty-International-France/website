export PATH=/home/bas/.local/bin:$PATH
export AWS_ACCESS_KEY_ID=${CELLAR_ADDON_KEY_ID}
export AWS_SECRET_ACCESS_KEY=${CELLAR_ADDON_KEY_SECRET}

export RESTIC_REPOSITORY=s3:https://${CELLAR_ADDON_HOST}/${BUCKET_NAME}
export DOCROOT=${APP_HOME}${CC_WEBROOT}/
