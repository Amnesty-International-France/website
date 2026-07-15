# Tests PHP (PHPUnit)

## Approche

Ces tests ne démarrent pas WordPress (pas de DB, pas de `wp-load.php`) : le
fichier de code métier testé est requis directement, et seules les fonctions
WordPress/plugins qu'il appelle sont stubées.

- `tests/bootstrap.php` contient les stubs **génériques et réutilisables**
  entre suites (ex. `WP_CLI`, `get_field`). Chargé une fois pour toute la
  suite via l'attribut `bootstrap` de `phpunit.xml.dist`.
- Les stubs **spécifiques à un scénario** (fausses réponses Salesforce,
  fixtures en mémoire, etc.) restent dans le fichier de test concerné, pas
  dans `bootstrap.php`.

Si un nouveau test a besoin de stuber beaucoup de fonctions WP (plus d'une
quinzaine) ou du comportement de hooks (`add_filter`/`apply_filters`), envisager
d'introduire `brain/brain-monkey` plutôt que d'empiler des stubs manuels.

## Organisation par domaine

Chaque domaine métier a son propre testsuite dans `phpunit.xml.dist`, sur le
modèle de `Salesforce` :

```xml
<testsuite name="MonDomaine">
    <directory>tests/MonDomaine</directory>
</testsuite>
```

accompagné d'un script composer dédié (voir `composer.json`, ex.
`test:salesforce`) et de son ajout au job `php-checks` de la CI
(`.github/workflows/ci.yml`).

## Lancer les tests

```
composer run test:salesforce
# ou directement :
./vendor/bin/phpunit --testsuite Salesforce
```

## Toujours lancer un testsuite à la fois

Ne lancez jamais `./vendor/bin/phpunit` sans `--testsuite` (ou un script
composer dédié) : PHPUnit charge (`require`) le fichier de chaque test au
démarrage pour découvrir ses méthodes, même si les tests eux-mêmes ne
s'exécutent jamais ensemble. Or plusieurs suites font exprès de déclarer une
fonction du même nom que celle testée réellement par une autre (ex.
`get_local_user()` est une vraie fonction dans `tests/Petitions/` mais un faux
stub de scénario dans `tests/Salesforce/SalesforcePetitionBulkCsvTest.php`) -
les charger ensemble provoque un fatal "cannot redeclare". Chaque script
composer (`test:salesforce`, `test:donor-space`, `test:petitions`, ...) est
déjà scopé via `--testsuite` pour cette raison ; c'est la façon supportée de
lancer les tests, en local comme en CI.
