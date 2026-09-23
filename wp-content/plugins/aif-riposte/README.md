# AIF Riposte

## Français

### Présentation

AIF Riposte est un plugin WordPress développé pour le site Amnesty International France.

Il ajoute un type de contenu **Riposte** permettant de publier et d’afficher des victoires ou avancées obtenues dans le cadre des combats portés par Amnesty.

Le plugin est conçu pour fonctionner avec le thème **Humanity** et réutilise plusieurs composants existants du thème.

---

## Fonctionnalités

### Custom Post Type

Le plugin ajoute le CPT :

```text
riposte_victory
```

Caractéristiques :

* Archive publique
* Pas de page single exploitable
* Tri manuel via drag & drop
* Image mise en avant
* Titre
* Contenu
* Date personnalisée
* Lien externe optionnel
* Taxonomies :
  * `location` : taxonomie existante du site, utilisée comme filtre **Pays**
  * `riposte_theme` : taxonomie dédiée au plugin, utilisée comme filtre **Thématique**
  * `riposte_tag` : taxonomie dédiée au plugin, utilisée comme indication **Mot clé**

Les pages single ne sont pas destinées à être consultées. Toute tentative d’accès à une single Riposte est redirigée vers l’archive.

---

## Architecture

```text
aif-riposte/
│
├── aif-riposte.php
│
├── assets/
│   ├── css/
│   │   └── aif-riposte.css
│   ├── fonts/
│   │   ├── AgencyFB-Bold.woff
│   │   ├── AgencyFB-Bold.woff2
│   │   ├── AgencyFB-Reg.woff
│   │   ├── AgencyFB-Reg.woff2
│   │   ├── URWDIN-Regular.woff
│   │   └── URWDIN-Regular.woff2
│   └── js/
│       ├── admin-ordering.js
│       ├── admin-theme.js
│       ├── editor.js
│       └── load-more.js
│
├── includes/
│   ├── admin-ordering.php
│   ├── admin-taxonomies.php
│   ├── ajax-load-more.php
│   ├── archive.php
│   ├── assets.php
│   ├── breadcrumb.php
│   ├── card.php
│   ├── filters.php
│   ├── metaboxes.php
│   ├── post-type.php
│   ├── seo.php
│   ├── settings.php
│   └── template-loader.php
│
└── templates/
    ├── archive-riposte-victory.php
    └── partials/
        └── archive-filters.php
```

### Rôle des fichiers principaux

```text
aif-riposte.php
```

Fichier principal du plugin. Il définit les constantes, charge les fichiers nécessaires et gère l’activation/désactivation du plugin.

```text
includes/post-type.php
```

Déclare le CPT `riposte_victory`, déclare les taxonomies dédiées `riposte_theme` et `riposte_tag`, puis associe le CPT aux taxonomies `location`, `riposte_tag` et `riposte_theme`.

```text
includes/archive.php
```

Configure la requête principale de l’archive : nombre d’éléments, ordre, filtres de taxonomies.

```text
includes/filters.php
```

Centralise la liste des taxonomies filtrables, la normalisation des identifiants de termes et la construction de la tax_query partagée entre l’archive initiale et le chargement AJAX.

```text
includes/template-loader.php
```

Charge le template d’archive fourni par le plugin.

```text
templates/archive-riposte-victory.php
```

Template front de l’archive des Ripostes.

```text
templates/partials/archive-filters.php
```

Partial spécifique au plugin pour afficher les filtres de l’archive dans l’ordre souhaité : **Pays** puis **Thématique**.

```text
includes/card.php
```

Gère le rendu HTML des cartes et les classes de grille/couleurs calculées selon leur position.

```text
includes/ajax-load-more.php
```

Gère le chargement AJAX du bouton “Charger plus”.

```text
includes/admin-ordering.php
```

Gère le tri drag & drop dans l’administration.

```text
includes/metaboxes.php
assets/js/editor.js
```

Gèrent les champs personnalisés Gutenberg : date et lien externe.

```text
includes/settings.php
```

Ajoute la page de réglages du chapo d’archive.

```text
includes/assets.php
```

Charge les fichiers CSS et JS du plugin.

```text
includes/breadcrumb.php
```

Personnalise le fil d’Ariane de l’archive.

```text
includes/seo.php
```

Personnalise les éléments SEO de l’archive.

---

## Archive

L’archive est disponible via l’URL du CPT : /ripostes/

Elle est gérée par :

```text
templates/archive-riposte-victory.php
```

Le template réutilise certains composants du thème Humanity :

* Hero archive
* Footer du thème
* Styles et scripts des filtres existants

Le chapo est géré par le plugin via une option dédiée.

---

## Filtres

Les Ripostes peuvent être filtrées par :

* `location` : Pays
* `riposte_theme` : Thématique

La taxonomie `riposte_theme` est créée par le plugin.

Les filtres sont rendus via un partial plugin :

```text
templates/partials/archive-filters.php
```

Ce partial réutilise le partial du thème :

```text
partials/forms/taxonomy-filters.php
```

Cela permet de conserver :

* le markup des filtres Humanity ;
* les selects custom du thème ;
* les styles existants ;
* le JavaScript du thème.

Le partial plugin force également l’ordre d’affichage :

```text
Pays
Thématique
```

Paramètres utilisés :

```text
?qlocation=...
?qriposte_theme=...
```

La logique commune des filtres est centralisée dans :

```text
includes/filters.php
```

Ce fichier contient :

* la liste des taxonomies filtrables ;
* la normalisation des identifiants de termes ;
* la construction de la `tax_query`.

Les fichiers suivants récupèrent uniquement les valeurs propres à leur contexte avant d’appeler cette logique partagée :

```text
includes/archive.php
includes/ajax-load-more.php
```

Cela garantit un comportement identique entre le rendu initial et le chargement AJAX.

---

## Taxonomie Thématique

Le plugin ajoute une taxonomie dédiée :

```text
riposte_theme
```

Elle est rattachée uniquement au CPT :

```text
riposte_victory
```

Elle est déclarée dans :

```text
includes/post-type.php
```

Caractéristiques :

* non publique sur le front ;
* sans archive publique dédiée ;
* sans réécriture d’URL ;
* visible dans l’administration ;
* disponible dans Gutenberg via l’API REST.

---

## Taxonomie Mot clé

Le plugin ajoute une taxonomie dédiée :

```text
riposte_tag
```

Elle est rattachée uniquement au CPT :

```text
riposte_victory
```

Elle est déclarée dans :

```text
includes/post-type.php
```

Caractéristiques :

* non publique sur le front ;
* sans archive publique dédiée ;
* sans réécriture d’URL ;
* visible dans l’administration ;
* disponible dans Gutenberg via l’API REST.

---

## Limitation des taxonomies location, riposte_theme et riposte_tag

La limitation à une seule valeur est gérée par :

```text
assets/js/admin-theme.js
```
```text
includes/admin-taxonomies.php
```

La sélection unique est assurée à deux niveaux :

* `assets/js/admin-theme.js` limite immédiatement la sélection dans l’éditeur Gutenberg.
* `includes/admin-taxonomies.php` contrôle à nouveau les termes au moment de leur affectation et conserve le dernier terme transmis, comme l’interface Gutenberg.

La validation PHP garantit que la règle reste respectée même si le JavaScript ne s’exécute pas ou si le contenu est enregistré par une autre interface.

---

## Chapo d’archive

Une page de réglages est disponible dans l’administration :

```text
Ripostes > Réglages
```

Option utilisée :

```text
aif_riposte_archive_chapo
```

Le contenu est affiché dans le template d’archive, sous le hero.

---

## Champs personnalisés

### Date personnalisée

Chaque Riposte peut avoir une date spécifique.

Meta utilisée :

```text
aif_riposte_date
```

Cette meta est enregistrée via REST pour être modifiable dans Gutenberg.

La date est affichée dans :

```text
includes/card.php
```

Le formatage s’appuie sur le helper du thème Humanity quand il est disponible, afin de conserver le même comportement que les actualités.

### Lien externe

Chaque Riposte peut aussi recevoir un lien externe optionnel.

Meta utilisée :

```text
aif_riposte_external_url
```

Lorsque ce lien est renseigné :

* toute la carte devient cliquable ;
* le lien s’ouvre dans un nouvel onglet ;
* l’overlay de lien est géré par la classe `aif-riposte-card__link`.
* un texte « En savoir plus » est affiché et est accompagné de trois chevrons animés ;
* l’animation crée un mouvement progressif vers la droite afin de signaler que la carte est interactive ;
* l’animation est désactivée lorsque l’utilisateur demande une réduction des animations via `prefers-reduced-motion`.

Fichiers concernés :

```text
includes/metaboxes.php
assets/js/editor.js
includes/card.php
```

---

## Tri des contenus

L’ordre d’affichage repose sur le champ WordPress natif :

```text
menu_order
```

Les contenus sont réordonnables en drag & drop dans la liste d’administration du CPT.

Le tri modifiant un ordre global, il est réservé aux utilisateurs disposant de la capacité `edit_others_posts`. Une vérification `edit_post` est également réalisée pour chaque contenu traité.

Fichiers concernés :

```text
includes/admin-ordering.php
assets/js/admin-ordering.js
```

L’ordre est respecté sur le front et dans le chargement AJAX.

---

## Chargement AJAX

L’archive utilise un bouton :

```text
Charger plus
```

Le bouton charge les éléments suivants en AJAX.

Fichiers concernés :

```text
includes/ajax-load-more.php
assets/js/load-more.js
```

Le chargement repose sur une logique d’offset, et non sur une pagination classique. Cela permet de conserver le bon pattern de grille même lorsque le nombre d’éléments chargés varie selon le viewport.

La présence d’éléments supplémentaires est calculée à partir du nombre réel de contenus renvoyés par la requête, afin d’éviter un dernier chargement vide ou l’affichage inutile du bouton.

Le JavaScript transmet aussi les filtres actifs :

```text
qlocation
qriposte_theme
```

---

## Système de grille

Les cartes sont affichées dans une grille éditorialisée dont le comportement varie selon la taille d’écran.

### Mobile

Une seule carte par ligne :

```text
100%
100%
100%
```

### Tablette

Cycle de 8 cartes :

```text
Ligne 1 : 2 cartes à 50%
Ligne 2 : 1 carte à 100%
Ligne 3 : 2 cartes à 50%
Ligne 4 : 2 cartes à 50%
Ligne 5 : 1 carte à 100%
```

Puis le cycle recommence.

Les cartes pleine largeur reçoivent la classe :

```text
aif-riposte-card--tablet-full
```

et utilisent une présentation proche des cartes larges : image à gauche, texte à droite.

### Desktop

Cycle de 10 cartes :

```text
Ligne 1 : 3 cartes à 33%
Ligne 2 : 1 très large à 67% + 1 petite à 33%
Ligne 3 : 3 cartes à 33%
Ligne 4 : 2 moyennes à 50%
```

Puis le cycle recommence.

La grille repose sur :

* une grille CSS de 6 colonnes ;
* des classes calculées selon la position de la carte ;
* des couleurs attribuées automatiquement.

Fichiers concernés :

```text
includes/card.php
assets/css/aif-riposte.css
assets/js/load-more.js
```

Fonction principale :

```php
aif_riposte_get_card_layout_classes()
```

Important : le système de grille, les classes des cartes et le chargement AJAX sont liés. Toute modification du pattern d’affichage nécessite généralement des changements simultanés dans `includes/card.php`, `assets/css/aif-riposte.css` et `assets/js/load-more.js`.

---

## Contenu des cartes

Le rendu HTML des cartes est centralisé dans :

```text
includes/card.php
```

Les cartes peuvent afficher :

* le pays principal via `location` ;
* la date personnalisée ;
* le contenu éditorial ;
* un lien externe optionnel ;
* le texte “En savoir plus” si un lien externe est renseigné ;
* les thématiques via `riposte_theme`.
* le mot clé via `riposte_tag`.

Les images sont forcées au format carré via CSS.

Sur desktop :

* les petites cartes affichent l’image au-dessus du contenu ;
* les cartes moyennes et très larges affichent l’image à gauche et le contenu à droite ;
* la thématique et le mot clé sont affichés sous le séparateur lorsqu’ils sont renseignés.

---

## Polices

Le plugin charge ses propres polices depuis :

```text
assets/fonts/
```

Polices utilisées :

* `Agency FB` pour les textes des cartes ;
* `URW DIN` pour les contenus/excerpts des cartes.

Les déclarations `@font-face` se trouvent dans :

```text
assets/css/aif-riposte.css
```

---

## Couleurs

Les couleurs ne sont pas configurées au niveau des contenus ou des taxonomies.

Elles dépendent uniquement de la position de la carte dans la grille.

Classes générées :

```text
aif-riposte-card--color-1
aif-riposte-card--color-2
aif-riposte-card--color-3
aif-riposte-card--color-4
aif-riposte-card--color-5
```

Chaque couleur peut aussi définir des variantes pour :

* le fond de carte ;
* les tags ;
* le pays ;
* le lien “En savoir plus”.

Les couleurs sont définies dans :

```text
assets/css/aif-riposte.css
```

---

## Visibilité publique et recherche

L’archive `/ripostes/` reste publiquement accessible.

Les contenus individuels ne sont pas destinés à être consultés :

* les pages single sont redirigées en 301 vers l’archive ;
* les Ripostes sont exclues de la recherche générale WordPress avec `exclude_from_search` ;
* `publicly_queryable` reste activé afin de conserver l’accès à l’archive.

---

## SEO

Le plugin ajoute des ajustements SEO spécifiques à l’archive des Ripostes.

Fichier concerné :

```text
includes/seo.php
```

Ce fichier permet notamment de gérer :

* l’URL canonique de l’archive ;
* le titre SEO ;
* le titre Open Graph ;
* le titre Twitter.

Le fil d’Ariane est géré séparément dans :

```text
includes/breadcrumb.php
```

Lorsque la page `S’informer` existe avec le slug `sinformer`, le fil d’Ariane attendu est :

```text
Accueil > S’informer > Ripostes
```
Si cette page n’existe pas, le niveau parent n’est pas ajouté.

---

## Assets

Les assets sont répartis ainsi :

```text
assets/css/aif-riposte.css
assets/js/admin-ordering.js
assets/js/admin-theme.js
assets/js/editor.js
assets/js/load-more.js
assets/fonts/
```

Leur chargement est centralisé dans :

```text
includes/assets.php
```

---

## Traduction

Le plugin n’est pas prévu pour être traduit.

Les libellés sont pensés pour le site Amnesty International France.

---

## Dépendances

Le plugin dépend de plusieurs éléments fournis par le thème Humanity :

* la taxonomie `location` ;
* le pattern `amnesty/archive-hero` ;
* le template-part `footer` ;
* le partial `partials/forms/taxonomy-filters.php` ;
* les styles et scripts des filtres ;
* certaines variables CSS WordPress du thème ;
* le helper de formatage des dates lorsqu’il est disponible.

---

## Développement

### Ajouter un nouveau champ

1. Déclarer la meta dans :

```text
includes/metaboxes.php
```

2. Ajouter l’interface Gutenberg dans :

```text
assets/js/editor.js
```

3. Afficher la donnée dans :

```text
includes/card.php
```

---

### Modifier la carte

Le rendu HTML d’une carte est centralisé dans :

```text
includes/card.php
```

C’est ce fichier qu’il faut modifier pour changer :

* le markup HTML ;
* les données affichées ;
* les classes CSS ;
* le pattern de grille ;
* la logique des couleurs.

---

### Modifier la grille

La logique principale est dans :

```php
aif_riposte_get_card_layout_classes()
```

Fichier :

```text
includes/card.php
```

Styles :

```text
assets/css/aif-riposte.css
```

Si le pattern change, vérifier également :

```text
assets/js/load-more.js
```

car le nombre d’éléments chargés dépend du pattern de grille.

---

### Modifier les filtres

Les filtres de l’archive sont gérés par :

```text
templates/partials/archive-filters.php
```

Le partial impose actuellement l’ordre :

```text
location
riposte_theme
```

La liste des taxonomies filtrables et la construction de la `tax_query` sont centralisées dans :

```text
includes/filters.php
```

Si une nouvelle taxonomie doit devenir filtrable, elle doit être ajoutée dans ce fichier, puis transmise par :

```text
assets/js/load-more.js
```

Le rendu initial et le chargement AJAX réutiliseront ensuite automatiquement la même logique.

---

### Modifier le chargement AJAX

Les fichiers à maintenir ensemble sont :

```text
assets/js/load-more.js
includes/ajax-load-more.php
```

Le JS calcule combien d’éléments charger selon le viewport et le pattern courant.

Le PHP récupère les contenus à partir de l’offset transmis.

---

### Modifier le SEO

Les ajustements SEO sont dans :

```text
includes/seo.php
```

Le breadcrumb est dans :

```text
includes/breadcrumb.php
```

---

## Mise en production

Après le déploiement du plugin en production, effectuer les opérations suivantes.

### 1. Activer le plugin

Dans l’administration WordPress, activer :

```text
AIF Riposte
```

### 2. Actualiser les règles de réécriture

Le plugin actualise les règles de réécriture lors de son activation.

En cas de page `/ripostes/` inaccessible ou de réponse 404 après le déploiement, aller dans :

```text
Réglages > Permaliens
```

puis cliquer sur **Enregistrer les modifications** sans modifier les réglages.

### 3. Créer les termes nécessaires

Créer les termes qui seront utilisés par les contenus :

```text
Ripostes > Thématiques
Ripostes > Mots clés
```

Vérifier également que les pays nécessaires existent déjà dans la taxonomie `location` fournie par le thème Humanity.

### 4. Configurer le chapo de l’archive

Aller dans :

```text
Ripostes > Réglages
```

et renseigner le texte d’introduction affiché sous le hero de l’archive.

### 5. Créer et ordonner les Ripostes

Pour chaque Riposte, renseigner :

* le titre ;
* le contenu ;
* l’image mise en avant ;
* la date affichée sur la carte ;
* le lien externe, lorsqu’il existe ;
* un seul pays ;
* une seule thématique ;
* un seul mot clé.

Réordonner ensuite les contenus par drag & drop depuis la liste d’administration si nécessaire.

### 6. Ajouter l’archive à la navigation

Ajouter la page d’archive au menu ou aux liens du site si elle doit être accessible depuis la navigation principale :

```text
/ripostes/
```

Aucune page WordPress distincte nommée « Ripostes » ne doit être créée : l’URL est fournie par l’archive du Custom Post Type.

### 7. Vérifier la page en production

Contrôler notamment :

* l’accès à `/ripostes/` ;
* l’affichage du hero et du chapo ;
* l’ordre des filtres **Pays**, puis **Thématique** ;
* le fonctionnement de chaque filtre ;
* le bouton « Charger plus » sur mobile, tablette et desktop ;
* la continuité du pattern de grille et des couleurs après un chargement AJAX ;
* les liens externes et leurs effets interactifs ;
* la redirection des URLs single vers l’archive ;
* l’absence des Ripostes dans la recherche générale du site ;
* le fil d’Ariane ;
* le titre SEO, les métadonnées sociales et l’URL canonique.

### 8. Purger les caches

Après validation, purger les éventuels caches applicatifs, serveur, CDN et navigateur afin de prendre en compte les nouveaux templates et assets.

---

## English

### Overview

AIF Riposte is a WordPress plugin developed for the Amnesty International France website.

It adds a **Riposte** custom post type used to publish and display victories or progress achieved through campaigns led by Amnesty.

The plugin is designed to work with the **Humanity** theme and reuses several existing components provided by the theme.

---

## Features

### Custom Post Type

The plugin adds the following CPT:

```text
riposte_victory
```

Characteristics:

* Public archive
* No usable single page
* Manual drag & drop ordering
* Featured image
* Title
* Content
* Custom date
* Optional external link
* Taxonomies:
  * `location`: existing site taxonomy, used as the **Country** filter
  * `riposte_theme`: taxonomy provided by the plugin, used as the **Theme** filter
  * `riposte_tag`: taxonomy provided by the plugin, used as the **Keyword** indicator

Single pages are not intended to be accessed directly. Any attempt to access a Riposte single page is redirected to the archive.

---

## Architecture

```text
aif-riposte/
│
├── aif-riposte.php
│
├── assets/
│   ├── css/
│   │   └── aif-riposte.css
│   ├── fonts/
│   │   ├── AgencyFB-Bold.woff
│   │   ├── AgencyFB-Bold.woff2
│   │   ├── AgencyFB-Reg.woff
│   │   ├── AgencyFB-Reg.woff2
│   │   ├── URWDIN-Regular.woff
│   │   └── URWDIN-Regular.woff2
│   └── js/
│       ├── admin-ordering.js
│       ├── admin-theme.js
│       ├── editor.js
│       └── load-more.js
│
├── includes/
│   ├── admin-ordering.php
│   ├── admin-taxonomies.php
│   ├── ajax-load-more.php
│   ├── archive.php
│   ├── assets.php
│   ├── breadcrumb.php
│   ├── card.php
│   ├── filters.php
│   ├── metaboxes.php
│   ├── post-type.php
│   ├── seo.php
│   ├── settings.php
│   └── template-loader.php
│
└── templates/
    ├── archive-riposte-victory.php
    └── partials/
        └── archive-filters.php
```

### Main files

```text
aif-riposte.php
```

Main plugin file. It defines the plugin constants, loads the required files, and handles plugin activation and deactivation.

```text
includes/post-type.php
```

Registers the `riposte_victory` custom post type, registers the dedicated `riposte_theme` and `riposte_tag` taxonomies, then associates the CPT with the `location`, `riposte_tag`, and `riposte_theme` taxonomies.

```text
includes/archive.php
```

Configures the main archive query: number of posts, ordering, and taxonomy filters.

```text
includes/filters.php
```

Centralizes the list of filterable taxonomies, term identifier normalization, and the shared `tax_query` generation used by both the initial archive rendering and AJAX loading.

```text
includes/template-loader.php
```

Loads the archive template provided by the plugin.

```text
templates/archive-riposte-victory.php
```

Frontend archive template for Ripostes.

```text
templates/partials/archive-filters.php
```

Plugin-specific partial used to display the archive filters in the desired order: **Country** followed by **Theme**.

```text
includes/card.php
```

Handles the HTML rendering of cards as well as the grid and color classes computed according to each card's position.

```text
includes/ajax-load-more.php
```

Handles the AJAX "Load more" functionality.

```text
includes/admin-ordering.php
```

Handles drag & drop ordering within the WordPress administration area.

```text
includes/metaboxes.php
assets/js/editor.js
```

Manage the Gutenberg custom fields: custom date and external link.

```text
includes/settings.php
```

Adds the archive introduction settings page.

```text
includes/assets.php
```

Loads the plugin CSS and JavaScript assets.

```text
includes/breadcrumb.php
```

Customizes the archive breadcrumb.

```text
includes/seo.php
```

Customizes the archive SEO metadata.

---

## Archive

The archive is available through the CPT URL:

```text
/ripostes/
```

It is handled by:

```text
templates/archive-riposte-victory.php
```

The template reuses several components from the Humanity theme:

* Archive hero
* Theme footer
* Existing filter styles and scripts

The archive introduction is managed by the plugin through a dedicated option.

---

## Filters

Ripostes can be filtered by:

* `location`: Country
* `riposte_theme`: Theme

The `riposte_theme` taxonomy is created by the plugin.

Filters are rendered through the plugin partial:

```text
templates/partials/archive-filters.php
```

This partial reuses the theme partial:

```text
partials/forms/taxonomy-filters.php
```

This preserves:

* the Humanity filter markup;
* the theme's custom select elements;
* the existing styles;
* the theme JavaScript.

The plugin partial also enforces the display order:

```text
Country
Theme
```

Query parameters used:

```text
?qlocation=...
?qriposte_theme=...
```

The shared filtering logic is centralized in:

```text
includes/filters.php
```

This file contains:

* the list of filterable taxonomies;
* term identifier normalization;
* `tax_query` generation.

The following files only retrieve the values specific to their own context before calling this shared logic:

```text
includes/archive.php
includes/ajax-load-more.php
```

This guarantees identical behavior between the initial page rendering and AJAX loading.

---

## Theme Taxonomy

The plugin adds a dedicated taxonomy:

```text
riposte_theme
```

It is attached exclusively to the following CPT:

```text
riposte_victory
```

It is registered in:

```text
includes/post-type.php
```

Characteristics:

* not publicly available on the frontend;
* no dedicated public archive;
* no URL rewrite;
* visible in the WordPress administration;
* available in Gutenberg through the REST API.

---

## Keyword Taxonomy

The plugin adds a dedicated taxonomy:

```text
riposte_tag
```

It is attached exclusively to:

```text
riposte_victory
```

It is registered in:

```text
includes/post-type.php
```

Characteristics:

* not publicly available on the frontend;
* no dedicated public archive;
* no URL rewrite;
* visible in the WordPress administration;
* available in Gutenberg through the REST API.

---

## Restricting the location, riposte_theme and riposte_tag taxonomies

The one-term limitation is handled by:

```text
assets/js/admin-theme.js
```

```text
includes/admin-taxonomies.php
```

Single-term selection is enforced at two levels:

* `assets/js/admin-theme.js` immediately limits the selection within the Gutenberg editor.
* `includes/admin-taxonomies.php` validates the assigned terms again when they are saved and keeps only the last submitted term, matching Gutenberg's behavior.

The PHP validation guarantees that this rule is still enforced even if JavaScript is disabled or the content is saved through another interface.

---

## Archive Introduction

A settings page is available in the WordPress administration:

```text
Ripostes > Settings
```

Option used:

```text
aif_riposte_archive_chapo
```

The content is displayed in the archive template, directly below the hero section.

---

## Custom Fields

### Custom Date

Each Riposte can have its own custom date.

Meta key:

```text
aif_riposte_date
```

This meta field is registered through the REST API so it can be edited directly in Gutenberg.

The date is displayed in:

```text
includes/card.php
```

Formatting relies on the Humanity theme helper whenever it is available, ensuring consistent behavior with the site's news posts.

### External Link

Each Riposte may also include an optional external link.

Meta key:

```text
aif_riposte_external_url
```

When this link is provided:

* the entire card becomes clickable;
* the link opens in a new browser tab;
* the clickable overlay is handled by the `aif-riposte-card__link` class;
* a "Learn more" label is displayed together with three animated chevrons;
* the animation creates a progressive movement toward the right to indicate that the card is interactive;
* the animation is disabled whenever the user requests reduced motion through `prefers-reduced-motion`.

Related files:

```text
includes/metaboxes.php
assets/js/editor.js
includes/card.php
```

---

## Content Ordering

The display order relies on the native WordPress field:

```text
menu_order
```

Content can be reordered via drag & drop from the CPT administration list.

Since this modifies the global display order, the feature is restricted to users with the `edit_others_posts` capability. An additional `edit_post` permission check is also performed for each processed post.

Related files:

```text
includes/admin-ordering.php
assets/js/admin-ordering.js
```

The order is respected both on the frontend and during AJAX loading.

---

## AJAX Loading

The archive uses a:

```text
Load more
```

button.

The button loads the next set of items via AJAX.

Related files:

```text
includes/ajax-load-more.php
assets/js/load-more.js
```

Loading is based on an offset system rather than traditional pagination. This ensures that the grid layout remains consistent regardless of the number of items loaded for each viewport size.

The presence of additional content is calculated from the actual number of posts returned by the query, preventing unnecessary empty requests and avoiding the display of the button when no more content is available.

The JavaScript also sends the currently active filters:

```text
qlocation
qriposte_theme
```

---

## Grid System

Cards are displayed using an editorial grid whose layout varies depending on the screen size.

### Mobile

One card per row:

```text
100%
100%
100%
```

### Tablet

8-card cycle:

```text
Row 1: 2 cards at 50%
Row 2: 1 card at 100%
Row 3: 2 cards at 50%
Row 4: 2 cards at 50%
Row 5: 1 card at 100%
```

The cycle then repeats.

Full-width cards receive the following class:

```text
aif-riposte-card--tablet-full
```

and use a layout similar to large cards, with the image on the left and the content on the right.

### Desktop

10-card cycle:

```text
Row 1: 3 cards at 33%
Row 2: 1 extra-wide card at 67% + 1 small card at 33%
Row 3: 3 cards at 33%
Row 4: 2 medium cards at 50%
```

The cycle then repeats.

The grid is based on:

* a 6-column CSS Grid;
* classes calculated from each card's position;
* automatically assigned color variants.

Related files:

```text
includes/card.php
assets/css/aif-riposte.css
assets/js/load-more.js
```

Main function:

```php
aif_riposte_get_card_layout_classes()
```

**Important:** the grid system, card classes and AJAX loading are tightly coupled. Any change to the layout pattern will generally require coordinated updates in `includes/card.php`, `assets/css/aif-riposte.css`, and `assets/js/load-more.js`.

---

## Card Content

The HTML rendering of cards is centralized in:

```text
includes/card.php
```

Cards may display:

* the main country from the `location` taxonomy;
* the custom date;
* the editorial content;
* an optional external link;
* the **Learn more** label when an external link is provided;
* themes from the `riposte_theme` taxonomy;
* the keyword from the `riposte_tag` taxonomy.

Images are forced to a square format through CSS.

On desktop:

* small cards display the image above the content;
* medium and extra-wide cards display the image on the left and the content on the right;
* the theme and keyword are displayed below the separator when available.

---

## Fonts

The plugin loads its own fonts from:

```text
assets/fonts/
```

Fonts used:

* `Agency FB` for card headings;
* `URW DIN` for card body content and excerpts.

The `@font-face` declarations are located in:

```text
assets/css/aif-riposte.css
```

---

## Colors

Colors are not configured at the content or taxonomy level.

They depend solely on the card's position within the grid.

Generated classes:

```text
aif-riposte-card--color-1
aif-riposte-card--color-2
aif-riposte-card--color-3
aif-riposte-card--color-4
aif-riposte-card--color-5
```

Each color may also define variants for:

* the card background;
* tags;
* the country label;
* the **Learn more** link.

Colors are defined in:

```text
assets/css/aif-riposte.css
```

---

## Public Visibility and Search

The `/ripostes/` archive remains publicly accessible.

Individual posts are not intended to be viewed directly:

* single pages are permanently redirected (301) to the archive;
* Ripostes are excluded from the default WordPress search using `exclude_from_search`;
* `publicly_queryable` remains enabled so the archive itself stays accessible.

---

## SEO

The plugin provides SEO customizations specifically for the Riposte archive.

Related file:

```text
includes/seo.php
```

This file manages:

* the archive canonical URL;
* the SEO title;
* the Open Graph title;
* the Twitter title.

The breadcrumb is handled separately in:

```text
includes/breadcrumb.php
```

When the **S’informer** page exists with the slug `sinformer`, the expected breadcrumb is:

```text
Home > S’informer > Ripostes
```

If this page does not exist, the parent level is simply omitted.

---

## Assets

The plugin assets are organized as follows:

```text
assets/css/aif-riposte.css
assets/js/admin-ordering.js
assets/js/admin-theme.js
assets/js/editor.js
assets/js/load-more.js
assets/fonts/
```

Their loading is centralized in:

```text
includes/assets.php
```

---

## Translation

The plugin is not intended to be translated.

All labels are designed specifically for the Amnesty International France website.

---

## Dependencies

The plugin depends on several components provided by the Humanity theme:

* the `location` taxonomy;
* the `amnesty/archive-hero` pattern;
* the `footer` template part;
* the `partials/forms/taxonomy-filters.php` partial;
* the filter styles and scripts;
* several WordPress CSS variables defined by the theme;
* the theme date formatting helper, when available.

---

## Development

### Adding a New Custom Field

1. Register the meta field in:

```text
includes/metaboxes.php
```

2. Add the Gutenberg interface in:

```text
assets/js/editor.js
```

3. Display the data in:

```text
includes/card.php
```

---

### Modifying a Card

The HTML rendering of a card is centralized in:

```text
includes/card.php
```

This is the file to modify when changing:

* the HTML markup;
* the displayed data;
* the CSS classes;
* the grid layout;
* the color assignment logic.

---

### Modifying the Grid

The main layout logic is located in:

```php
aif_riposte_get_card_layout_classes()
```

File:

```text
includes/card.php
```

Styles:

```text
assets/css/aif-riposte.css
```

If the layout pattern changes, also verify:

```text
assets/js/load-more.js
```

since the number of items loaded depends on the grid pattern.

---

### Modifying the Filters

Archive filters are managed by:

```text
templates/partials/archive-filters.php
```

The partial currently enforces the following order:

```text
location
riposte_theme
```

The list of filterable taxonomies and the `tax_query` generation are centralized in:

```text
includes/filters.php
```

If a new taxonomy needs to become filterable, it must first be added to this file, then passed through:

```text
assets/js/load-more.js
```

The initial page rendering and AJAX loading will then automatically reuse the same filtering logic.

---

### Modifying AJAX Loading

The following files must always be maintained together:

```text
assets/js/load-more.js
includes/ajax-load-more.php
```

The JavaScript calculates how many items should be loaded depending on the current viewport and grid pattern.

PHP retrieves the corresponding posts from the transmitted offset.

---

### Modifying SEO

SEO customizations are located in:

```text
includes/seo.php
```

The breadcrumb is handled in:

```text
includes/breadcrumb.php
```

---

## Production Deployment

After deploying the plugin to production, perform the following steps.

### 1. Activate the Plugin

From the WordPress administration area, activate:

```text
AIF Riposte
```

### 2. Refresh Rewrite Rules

The plugin automatically refreshes rewrite rules upon activation.

If the `/ripostes/` archive returns a 404 error or is inaccessible after deployment, go to:

```text
Settings > Permalinks
```

and click **Save Changes** without modifying any settings.

### 3. Create the Required Terms

Create the taxonomy terms that will be used by the content:

```text
Ripostes > Themes
Ripostes > Keywords
```

Also verify that all required countries already exist in the `location` taxonomy provided by the Humanity theme.

### 4. Configure the Archive Introduction

Go to:

```text
Ripostes > Settings
```

and enter the introduction text displayed below the archive hero.

### 5. Create and Order Ripostes

For each Riposte, provide:

* the title;
* the content;
* the featured image;
* the date displayed on the card;
* the external link, when applicable;
* one country only;
* one theme only;
* one keyword only.

If necessary, reorder the content via drag & drop from the administration list.

### 6. Add the Archive to the Navigation

Add the archive URL to the site's navigation menu or links if it should be accessible from the main navigation:

```text
/ripostes/
```

Do **not** create a separate WordPress page named **Ripostes**. The URL is automatically provided by the Custom Post Type archive.

### 7. Verify the Production Page

Check in particular:

* access to `/ripostes/`;
* the display of the hero and archive introduction;
* the filter order (**Country** followed by **Theme**);
* each filter's behavior;
* the **Load more** button on mobile, tablet and desktop;
* the continuity of the grid pattern and color sequence after AJAX loading;
* external links and their interactive behavior;
* the redirection of single URLs to the archive;
* the absence of Ripostes from the site's global search;
* the breadcrumb;
* the SEO title, social metadata and canonical URL.

### 8. Clear All Caches

After validation, clear any application, server, CDN and browser caches to ensure that the new templates and assets are properly loaded.

---