# Sentry — suivi d'erreurs

## Vue d'ensemble

Le suivi d'erreurs est assuré par le plugin WordPress **[WP Sentry Integration](https://wordpress.org/plugins/wp-sentry-integration/)**, qui gère à la fois les erreurs PHP (backend) et les erreurs JS (frontend) via une configuration unique dans `wp-config.php`.

## Configuration

Toute la configuration passe par des constantes définies dans `wp-config.php`, alimentées par des variables d'environnement. Aucun secret n'est commité dans le code.

### Variables d'environnement

| Variable | Description |
|---|---|
| `PHP_DSN` | DSN Sentry pour le tracking PHP |
| `JS_DSN` | DSN Sentry pour le tracking JS (peut être identique à `PHP_DSN`) |
| `WP_ENVIRONMENT_TYPE` | Environnement : `production`, `staging` ou `development` |
| `SENTRY_RELEASE` | Référence de la release (hash de commit) |

Laisser `PHP_DSN` et `JS_DSN` vides désactive silencieusement Sentry (aucune erreur levée).

### Constantes wp-config.php

```php
define('WP_SENTRY_PHP_DSN',              getenv('PHP_DSN'));
define('WP_SENTRY_BROWSER_DSN',          getenv('JS_DSN'));
define('WP_SENTRY_ENV',                  getenv('WP_ENVIRONMENT_TYPE') ?: null);
define('WP_SENTRY_VERSION',              getenv('SENTRY_RELEASE') ?: 'unknown');
define('WP_SENTRY_SEND_DEFAULT_PII',     false);
define('WP_SENTRY_TRACES_SAMPLE_RATE',   0.1);
define('WP_SENTRY_PROFILES_SAMPLE_RATE', 0.1);
define('WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE', 0.1);
define('WP_SENTRY_ERROR_TYPES',          E_ALL & ~E_NOTICE & ~E_USER_NOTICE);

define('WP_SENTRY_CLIENTBUILDER_CALLBACK', function (\Sentry\ClientBuilder $builder): void {
    $original = $builder->getOptions()->getBeforeSendCallback();
    $builder->getOptions()->setBeforeSendCallback(function (\Sentry\Event $event, $hint) use ($original): ?\Sentry\Event {
        // Bloque tout envoi en développement
        if ('development' === WP_SENTRY_ENV) {
            return null;
        }
        // Supprime le corps des requêtes POST (données de formulaire, RGPD)
        $request = $event->getRequest();
        if (!empty($request['data'])) {
            $request['data'] = '[Filtered]';
            $event->setRequest($request);
        }
        return $original($event, $hint);
    });
});
```

## Ajouter le DSN par environnement

### En local (développement)

Laisser `PHP_DSN` et `JS_DSN` vides dans `.env`. Sentry ne s'initialise pas.

### Sur Infomaniak (production / staging)

Ajouter les variables dans le panel Infomaniak ou directement dans le `wp-config.php` du serveur via FTP/SSH :

```
PHP_DSN=https://xxxxx@oXXXXXX.ingest.sentry.io/XXXXXXX
JS_DSN=https://xxxxx@oXXXXXX.ingest.sentry.io/XXXXXXX
WP_ENVIRONMENT_TYPE=production
SENTRY_RELEASE=<hash-du-commit-ou-numéro-de-version>
```

`SENTRY_RELEASE` est mis à jour automatiquement à chaque déploiement par les workflows GitHub Actions (`deploy-prod.yaml`, `deploy-release.yml`), qui écrivent le SHA du commit dans le `.env` du serveur. Aucune action manuelle n'est nécessaire.

⚠️ **Pour le premier déploiement, s'assurer que la ligne `SENTRY_RELEASE=` existe déjà dans le `.env` serveur (valeur vide suffit).**

## Comportement par environnement

| Environnement | Erreurs envoyées | Traces perf |
|---|---|---|
| `production` | ✅ 100 % | 10 % |
| `staging` | ✅ 100 % | 10 % |
| `development` | ❌ bloqué (`before_send`) | — |

## RGPD

Deux niveaux de protection :

1. **`before_send`** — le corps des requêtes POST est remplacé par `[Filtered]` avant tout envoi, ce qui couvre les données saisies dans les formulaires (WooCommerce, Jetpack, pétitions).
2. **`WP_SENTRY_SEND_DEFAULT_PII false`** — le SDK ne transmet ni adresse IP, ni cookies, ni en-têtes HTTP sensibles.

Un troisième niveau est appliqué côté thème dans `functions.php` : le plugin expose par défaut la version de WordPress dans le contexte JS public, ce qui peut aider à identifier des versions vulnérables. Ce filtre le supprime :

```php
add_filter('wp_sentry_public_context', function(array $context): array {
    unset($context['tags']['wordpress']);
    return $context;
});
```

Pour les valeurs de variables qui pourraient apparaître dans les stack traces (ex : `$email`, `$password`), activer le **Data Scrubbing** dans les paramètres du projet Sentry : *Settings → Security & Privacy → Data Scrubbing*, avec des patterns comme `*email*`, `*password*`, `*name*`.

## Fichiers concernés

```
wp-config.php                                  ← constantes de configuration
wp-content/plugins/wp-sentry-integration/      ← plugin (géré via back-office WP)
.env.example                                   ← variables à renseigner par environnement
docs/contributors/sentry.md                                 ← ce fichier
```
