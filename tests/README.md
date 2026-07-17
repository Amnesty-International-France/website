# Tests automatisés

Le projet a trois couches de tests automatisés, indépendantes les unes des
autres :

| Couche | Outil | Où | Lancer |
|---|---|---|---|
| PHP unitaire | PHPUnit | `tests/<Domaine>/` | `composer run test:<domaine>` |
| JS unitaire | Vitest | `private/tests/scripts/modules/` | `cd private && yarn test` |
| End-to-end | Playwright | `private/tests/e2e/` | voir [section dédiée](#tests-e2e-playwright) |

Tout tourne aussi en CI (`.github/workflows/ci.yml`, jobs `php-checks` et
`frontend-checks`) avec exactement les mêmes commandes que ci-dessous.

## Tests PHP (PHPUnit)

### Approche

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

### Organisation par domaine

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

### Lancer les tests

```
composer install # une seule fois, installe phpunit dans vendor/
composer run test:salesforce
composer run test:donor-space
composer run test:petitions
# ou directement :
./vendor/bin/phpunit --testsuite Salesforce
```

### Toujours lancer un testsuite à la fois

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

## Tests JS unitaires (Vitest)

### Où et comment

Les tests vivent dans `private/tests/scripts/modules/`, un fichier
`<module>.test.js` par module de `private/src/scripts/modules/<module>.js`.
Environnement `jsdom` (voir `private/vitest.config.js`).

```
cd private
yarn install
yarn test                                          # toute la suite
yarn test tests/scripts/modules/mon-module.test.js  # un seul fichier
```

### Convention

Le module est importé directement (pas de mock du bundle) ; le DOM nécessaire
est construit à la main via `document.body.innerHTML`, puis on déclenche de
vrais événements (`dispatchEvent`) plutôt que d'appeler les handlers
directement, pour rester proche de ce qu'un navigateur ferait réellement. Voir
`private/tests/scripts/modules/turnstile.test.js` ou
`donation-calculator.test.js` comme référence.

Point d'attention ESLint : muter une propriété d'un élément DOM passé en
paramètre d'une fonction de test (`input.value = x`) déclenche la règle
`no-param-reassign` d'`airbnb-base`. C'est un mutation intentionnelle et sûre
dans ce contexte : ajouter un commentaire `eslint-disable-next-line
no-param-reassign` plutôt que contourner via `setAttribute()`, qui ne reflète
pas forcément la propriété `.value` réelle une fois qu'elle a été modifiée par
ailleurs (cf. l'historique git de `donation-calculator.test.js`).

## Tests E2E (Playwright)

### Prérequis

Docker doit tourner localement (`wp-env` orchestre un conteneur WordPress +
MySQL). Depuis `private/` :

```
yarn install
yarn playwright install --with-deps chromium   # une seule fois
```

Si `yarn` local ne correspond pas à la version épinglée
(`packageManager: yarn@…` dans `package.json`), `yarn install` (sans
`--immutable`) régénère `yarn.lock`/`.yarnrc.yml` localement ; ne pas commiter
ces changements incidents (`git checkout -- yarn.lock .yarnrc.yml` après coup).

### Lancer les tests

```
cd private
yarn env:e2e:start                              # démarre wp-env (~1-2 min)
./tests/e2e/support/seed-wordpress.sh            # seed le contenu nécessaire
yarn test:e2e --grep-invert @cloudflare-smoke    # suite déterministe (= CI)
yarn env:e2e:stop                                # coupe l'environnement
```

- `seed-wordpress.sh` est idempotent (vérifie l'existence avant de créer),
  mais `wp-env` persiste son volume Docker entre deux `env:e2e:start` : en cas
  de doute sur l'état de la base (contenu d'un ancien run qui traîne), repartir
  d'un environnement propre avec `yarn env:e2e:destroy` puis `env:e2e:start`.
- Le tag `@cloudflare-smoke` (`turnstile-cloudflare-dummy.spec.mjs`) fait un
  vrai appel réseau vers l'infrastructure de test Cloudflare ; il est exclu du
  run déterministe (local comme CI) et ne se lance qu'explicitement
  (`RUN_CLOUDFLARE_SMOKE=1`).
- Une nouvelle page/contenu nécessaire à un test se seed dans
  `seed-wordpress.sh` via `$wpdb->insert()` direct plutôt que
  `wp_insert_post()`/`wp post create` : plusieurs hooks `save_post`/
  `acf/save_post` du thème déclenchent de vrais appels à l'API Salesforce
  (ex. `create_petition()`), qu'on ne veut surtout pas exécuter pendant le
  seed.

### Jetpack est installé, mais seulement pour ce qui marche hors-ligne

`.wp-env.e2e.json` installe le plugin Jetpack vendu dans le dépôt
(`wp-content/plugins/jetpack`) et active son module **"contact-form"** au
démarrage. Jetpack détecte "localhost" comme environnement hors-ligne/dev
(aucun compte ni connexion requis), ce qui suffit à faire fonctionner
réellement les formulaires `jetpack/contact-form` (legs, fondation) - y
compris leur vraie soumission AJAX, pas juste leur affichage.

Cette astuce **ne marche pas pour tous les modules** : Jetpack désactive
explicitement, en mode hors-ligne, tout module marqué `Requires Connection:
Yes` dans son fichier (`wp-content/plugins/jetpack/class.jetpack.php`,
recherche `requires_connection`). C'est le cas du module "Search" (Instant
Search), qui est payant et nécessite un vrai compte connecté - impossible à
débloquer de la même façon. Voir `search.spec.mjs` et la section suivante pour
comment la recherche est testée à la place.

Deux pièges rencontrés en écrivant les tests de formulaires Jetpack, à
connaître avant d'en ajouter un nouveau :
- Le `<label>` d'un champ obligatoire embarque un texte caché
  "(obligatoire)" - une recherche par libellé exact (`getByLabel('Nom',
  { exact: true })`) peut échouer ou devenir ambiguë avec un autre champ dont
  le nom est un sous-texte (ex. "Nom" vs "Prénom"). Utiliser un regex ancré
  (`/^Nom/`) plutôt qu'une correspondance exacte.
- Un champ de consentement RGPD (`type="consent"`) est **implicite** par
  défaut (case cachée, pré-remplie, non interactive) : sa valeur n'est pas
  synchronisée avec l'état de validation côté client et fait toujours
  échouer la soumission. Ajouter `consentType="explicit"` dans le shortcode
  `[contact-field]` pour obtenir une vraie case à cocher testable.

### Recherche : un `wp_template` seedé plutôt que Jetpack

Le thème n'a pas de fichier `templates/search.html` ; sans lui, une recherche
retombe sur `index.html` (un simple bloc `post-content` sans boucle de
requête), qui affiche un contenu incohérent (parfois l'accueil, parfois
rien). `seed-wordpress.sh` seed un post `wp_template` (post_type
`wp_template` + terme de taxonomie `wp_theme`) pour le slot "search" -
exactement ce que ferait l'éditeur de site WordPress. Un `wp_template` en
base est toujours prioritaire sur les fichiers du thème, donc ça corrige
l'affichage sans toucher au moindre fichier du thème. Son contenu réutilise
la pattern `amnesty/search-results`, déjà fonctionnelle et indépendante de
Jetpack.

### Ajouter un nouveau test e2e

1. Si le parcours nécessite une page/un contenu qui n'existe pas par défaut,
   l'ajouter dans `seed-wordpress.sh` (voir les points d'attention ci-dessus).
2. Écrire le fichier `<parcours>.spec.mjs` dans `private/tests/e2e/`, en
   important `{ expect, test }` depuis `./support/fixtures` (pas directement
   `@playwright/test`) pour bénéficier de `gotoWithoutCookieOverlay`, qui
   masque le bandeau cookies OneTrust.
3. Vérifier réellement contre l'environnement avant de considérer le test
   fiable : démarrer `wp-env`, seed, lancer le fichier isolément
   (`yarn playwright test <fichier>.spec.mjs --project=chromium`), puis la
   suite complète pour détecter une régression.
