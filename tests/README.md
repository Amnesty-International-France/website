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
composer run test:salesforce-sync # ~30s, voir plus bas
# ou directement :
./vendor/bin/phpunit --testsuite Salesforce
```

### Toujours lancer un testsuite à la fois

Ne lancez jamais `./vendor/bin/phpunit` sans `--testsuite` (ou un script
composer dédié) : PHPUnit charge (`require`) le fichier de chaque test au
démarrage pour découvrir ses méthodes, même si les tests eux-mêmes ne
s'exécutent jamais ensemble. Or `get_local_user()`/`update_signature_status()`
sont de vraies fonctions dans `tests/Petitions/` (via `petitions/tables.php`)
mais un faux stub de scénario partagé par `tests/Salesforce/` et
`tests/SalesforceSync/` (`tests/support/local-user-stubs.php`) - charger
`Petitions` avec l'un des deux autres dans le même process provoque un fatal
"cannot redeclare", **peu importe l'ordre** (le stub partagé n'est délibérément
pas protégé par `function_exists()` : le protéger transformerait ce fatal en
un résultat faux silencieux selon l'ordre de chargement, ce qui est pire -
voir le commentaire en tête de `local-user-stubs.php`). Ce n'est pas
seulement `phpunit` bare qui est concerné : n'importe quel `--testsuite`
combinant `Petitions` avec `Salesforce` et/ou `SalesforceSync` fatal aussi
(`Salesforce,Petitions`, `Petitions,SalesforceSync`, etc.). Toute combinaison
qui n'inclut pas `Petitions` (ex. `Salesforce,SalesforceSync`,
`DonorSpace,SalesforceSync`) ou qui n'inclut pas `Salesforce`/`SalesforceSync`
(ex. `DonorSpace,Petitions`) reste, elle, sûre. Chaque script
composer (`test:salesforce`, `test:donor-space`, `test:petitions`, ...) est
déjà scopé via `--testsuite` pour cette raison ; c'est la façon supportée de
lancer les tests, en local comme en CI.

### Test d'intégration de la sync CLI Salesforce (`test:salesforce-sync`)

La signature d'une pétition (`amnesty_handle_petition_signature()`) n'appelle
jamais Salesforce en direct : elle écrit seulement en local, et la synchro
Salesforce est déférée à une tâche CLI séparée (`wp sync signatures` →
`Sync_Command::signatures()` → `sync_signatures_to_salesforce()` dans
`includes/salesforce/petition.php`). C'est ce job CLI, pas la requête HTTP de
signature, qui fait le vrai appel Salesforce - `tests/SalesforceSync/
SyncSignaturesToSalesforceTest.php` le teste de bout en bout (création du bulk
job, upload du CSV, fermeture du job, polling, lecture des résultats
succès/échec/non-traités, mise à jour du statut local de chaque signature),
contrairement à `tests/Salesforce/SalesforcePetitionBulkCsvTest.php` qui teste
chaque fonction isolément avec des entrées construites à la main.

Ce test a un coût réel et volontaire : `poll_job_state()` contient un vrai
`sleep(30)` (constante `SECONDS_BETWEEN_CHECKS`) entre deux vérifications de
statut du job, que rien ne peut stuber (fonction native PHP dans le namespace
global). Le job simulé passe à l'état `JobComplete` dès la première
vérification, donc ce coût est payé une seule fois (~30s) - c'est pourquoi ce
test vit dans son propre testsuite (`SalesforceSync`), séparé des autres
suites Salesforce qui restent rapides.

Ce fichier partage ses stubs de `get_local_user()`/`update_signature_status()`
avec `SalesforcePetitionBulkCsvTest.php` via `tests/support/local-user-stubs.php`
(`require_once`), plutôt que d'en déclarer chacun sa propre copie. Ce n'est
pas juste une question de style : deux copies distinctes, même identiques en
apparence, sont un piège de collision silencieuse. PHP ne garde que la
première déclaration chargée dans un process donné ; si les deux testsuites
se retrouvent un jour chargées dans le même process (ex.
`phpunit --testsuite Salesforce,SalesforceSync`), la seconde copie voit sa
fonction déjà définie (via son propre `function_exists()`) et n'installe
jamais la sienne - le test utilise alors silencieusement les globales de
l'*autre* fichier, sans fatal, juste un résultat faux. Un seul fichier
`require_once`-é par les deux évite ce problème par construction : quel que
soit l'ordre de chargement, les deux utilisent toujours la même définition.
Ce fichier partagé n'est volontairement pas dans `bootstrap.php` (toujours
chargé) pour la même raison que d'habitude : la vraie implémentation dans
`petitions/tables.php` est testée directement par
`tests/Petitions/PetitionsTablesTest.php`, et un stub partagé dans
`bootstrap.php` la masquerait en permanence.

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
débloquer de la même façon ; il n'y a pas de test e2e sur la recherche pour
cette raison.

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

### Parcours se terminant par un vrai appel Salesforce

Certains parcours font un vrai appel réseau synchrone à Salesforce pendant la
requête (ex. l'inscription newsletter, `patterns/page-nl-content.php`, qui
appelle `post_salesforce_users()` pour créer le Contact) - à ne pas confondre
avec des parcours qui ne font que des écritures locales et déclenchent leur
sync Salesforce plus tard via une tâche CLI séparée (c'est le cas de la
signature de pétition : `insert_petition_signature()` est local, la sync
Salesforce est un job `wp sync signatures` distinct, jamais appelé pendant la
requête HTTP).

`aif-e2e-support.php` mocke **tout** appel sortant vers Salesforce
(`includes/salesforce/data.php`/`authentification.php`, via la variable
d'environnement `AIF_SALESFORCE_URL` pointée vers une fausse URL) et
enregistre chaque appel (méthode, URL, corps) dans une option WordPress,
exposée en lecture/suppression via `/wp-json/aif-e2e/v1/salesforce-calls`
(voir `support/salesforce.mjs`). Un test peut donc :

```js
import { expect, test } from './support/fixtures';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

test.beforeEach(async ({ request, salesforceTestId }) => {
  await resetSalesforceCalls(request, salesforceTestId); // état propre entre deux tests
});

test('...', async ({ page, request, gotoWithoutCookieOverlay, salesforceTestId }) => {
  // ... remplir et soumettre le formulaire ...

  const calls = await getSalesforceCalls(request, salesforceTestId);
  const contactCall = calls.find((c) => c.method === 'POST' && c.url.includes('sobjects/Contact/'));
  expect(contactCall).toBeTruthy();
  expect(JSON.parse(contactCall.body).Email).toBe(uniqueEmail);
});
```

Par défaut, toute requête SOQL (`query/?q=`) mockée répond "aucun résultat"
(`totalSize: 0`), ce qui fait passer le code testé par sa branche "nouveau
contact/lead" - voir `newsletter-signup.spec.mjs` comme référence complète.

**Le journal des appels est isolé par test**, pas partagé dans une seule
option globale : Playwright peut exécuter plusieurs specs - ou le même spec
sur les projets `chromium`/`mobile-chromium` - en parallèle dans des workers
différents, contre le même `wp-env`. Sans isolation, deux tests touchant
Salesforce en même temps écraseraient/mélangeraient leurs appels respectifs
dans le même journal - un problème invisible tant qu'un seul test l'utilise,
mais qui rendrait le suivant flaky. La fixture `salesforceTestId`
(`support/fixtures.mjs`) génère un identifiant unique par test (l'id de test
Playwright) et l'envoie en header `X-AIF-E2E-Test-Id` sur toutes les requêtes
du navigateur ; `aif-e2e-support.php` s'en sert pour namespacer l'option
(`aif_e2e_salesforce_calls_<id>`) au lieu d'une option unique. Le fixture
`request` (client HTTP séparé du navigateur) n'hérite pas de ce header
automatiquement : passer explicitement `salesforceTestId` à
`getSalesforceCalls()`/`resetSalesforceCalls()` comme dans l'exemple
ci-dessus. Tout nouveau test qui vérifie un appel Salesforce doit faire de
même.

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
