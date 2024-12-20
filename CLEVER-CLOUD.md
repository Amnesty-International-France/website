# CLever-Cloud


## Préparation, installation d'un nouvel environnement

### Application et Add-ono
* Application PHP
* Une base de données Mysql
* Un File Storage FS Bucket
* Un Cellar S3 storage

#### Variables d'environnement de l'application PH

| Variable           | Valeur |
| --------           | -------|
| APP_ENV            | prod   |
| BUCKET_NAME | `aif-wp-backup-<env> ` |
| CC_FS_BUCKET       |`/www:<fs-bucket-url> ` |
| CC_POST_BUILD_HOOK |`clevercloud/post_build.sh`   |
| CC_PRE_BUILD_HOOK  |`curl "https://castor.jolicode.com/install" \| bash ` |
| CC_WEBROOT |/www |
| RESTIC_PASSWORD |random string |
| WP_ADMIN_EMAIL|email |
| WP_ADMIN_PASSWORD |random secure string|
| WP_ADMIN_USER |admin |
| WP_TITLE |AMNESTY FR |
| WP_URL |site url | 



## Baskup & restore

### Base de données

Clever Cloud fait des backup tous les jours

### Système de fichiers Wordpress

Nous utilisons Restic. Nous avons configuré une rétension de 30j, la cron est lancée à 0h00

#### L'initialisation de Restic se fait à la main, à la suite du premier déploiement:

```
clever applications list
clever link <application_id> --alias aif-wp-<env>
clever ssh --alias aif-wp-<env>
cd  $APP_ID
source infogerance/restic_source_me.sh
restic init
```

### Voire les snapshots disponibles

```
restic snapshots
```

### Récupérer un backup

```
export RESTORE_DIR=${HOME}/restore
rm rf ${RESTORE_DIR}
mkdir -p ${RESTORE_DIR}
restic restore --target ${RESTORE_DIR} <ID>
```



