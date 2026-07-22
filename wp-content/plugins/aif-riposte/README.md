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

La requête est ensuite filtrée dans :

```text
includes/archive.php
```

et dans :

```text
includes/ajax-load-more.php
```

pour conserver le même comportement lors du chargement AJAX.

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
* `includes/admin-taxonomies.php` contrôle à nouveau les termes lors de l’enregistrement du contenu.

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

Le fil d’Ariane attendu est :

```text
Accueil > S’informer > Ripostes
```

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

Si une nouvelle taxonomie doit être ajoutée, elle doit aussi être prise en compte dans :

```text
includes/archive.php
includes/ajax-load-more.php
assets/js/load-more.js
```

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

# English

## Overview

AIF Riposte is a WordPress plugin developed for the Amnesty International France website.

It adds a custom post type named **Riposte**, allowing editors to publish and display victories or significant progress achieved through Amnesty's campaigns.

The plugin has been specifically designed to work with the **Humanity** theme and reuses several of its existing components.

---

## Features

### Custom Post Type

The plugin registers the following custom post type:

```text
riposte_victory
```

Features:

* Public archive
* No accessible single pages
* Manual drag & drop ordering
* Featured image
* Title
* Content
* Custom date
* Optional external link
* Taxonomies:
  * `location`: existing site taxonomy used as the **Country** filter
  * `riposte_theme`: plugin taxonomy used as the **Theme** filter
  * `riposte_tag`: plugin taxonomy used as the **Keyword** indicator

Single pages are not intended to be viewed. Any attempt to access a Riposte single page is automatically redirected to the archive.

---

## Architecture

```text
aif-riposte/
│
├── aif-riposte.php
│
├── assets/
│   ├── css/
│   ├── fonts/
│   └── js/
│
├── includes/
│   ├── admin-ordering.php
│   ├── admin-taxonomies.php
│   ├── ajax-load-more.php
│   ├── archive.php
│   ├── assets.php
│   ├── breadcrumb.php
│   ├── card.php
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

---

## Main Files

### `aif-riposte.php`

Main plugin bootstrap file.

Loads all plugin components, defines constants and handles plugin activation/deactivation.

### `includes/post-type.php`

Registers:

* `riposte_victory`
* `riposte_theme`
* `riposte_tag`

and associates them with the existing `location` taxonomy.

### `includes/archive.php`

Configures the main archive query:

* ordering
* number of posts
* taxonomy filters

### `includes/template-loader.php`

Loads the archive template supplied by the plugin.

### `templates/archive-riposte-victory.php`

Main archive template.

### `templates/partials/archive-filters.php`

Plugin-specific filter partial forcing the filter order:

```text
Country
Theme
```

while reusing Humanity's existing filter component.

### `includes/card.php`

Generates the HTML markup for Riposte cards and computes their layout and color classes.

### `includes/ajax-load-more.php`

Handles AJAX loading for the **Load more** button.

### `includes/admin-ordering.php`

Implements drag & drop ordering in the WordPress administration.

### `includes/metaboxes.php`
### `assets/js/editor.js`

Manage the Gutenberg sidebar fields:

* custom date
* external URL

### `includes/settings.php`

Registers the archive introduction ("chapo") settings page.

### `includes/assets.php`

Centralizes CSS and JavaScript loading.

### `includes/breadcrumb.php`

Customizes the archive breadcrumb.

### `includes/seo.php`

Adds SEO customizations for the archive.

---

## Archive

The archive is available at:

```text
/ripostes/
```

It is rendered by:

```text
templates/archive-riposte-victory.php
```

The template reuses several Humanity components:

* archive hero
* theme footer
* existing archive filter styles and scripts

The archive introduction is managed through a dedicated plugin option.

---

## Filters

Ripostes can be filtered by:

* `location` (Country)
* `riposte_theme` (Theme)

The filters are rendered through:

```text
templates/partials/archive-filters.php
```

which internally reuses Humanity's:

```text
partials/forms/taxonomy-filters.php
```

This preserves:

* Humanity markup
* custom select components
* existing styles
* existing JavaScript

Filter parameters:

```text
?qlocation=
?qriposte_theme=
```

Filtering is applied both in:

```text
includes/archive.php
includes/ajax-load-more.php
```

ensuring identical behaviour during AJAX loading.

---

## Theme Taxonomy

The plugin registers:

```text
riposte_theme
```

Features:

* front-end private
* no public archive
* no URL rewrite
* visible in WordPress administration
* available through the REST API
* available in Gutenberg

---

## Keyword Taxonomy

The plugin registers:

```text
riposte_tag
```

Features:

* front-end private
* no public archive
* no URL rewrite
* visible in WordPress administration
* available through the REST API
* available in Gutenberg

---

## Single Taxonomy Selection

The following taxonomies are limited to **one selected term**:

* location
* riposte_theme
* riposte_tag

This behaviour is enforced at two levels:

* `assets/js/admin-theme.js` immediately limits the selection inside Gutenberg.
* `includes/admin-taxonomies.php` validates the selection again during save.

The PHP validation guarantees data consistency even if JavaScript is unavailable.

---

## Archive Introduction

A settings page is available:

```text
Ripostes → Settings
```

Stored option:

```text
aif_riposte_archive_chapo
```

The content is displayed below the archive hero.

---

## Custom Fields

### Custom Date

Meta key:

```text
aif_riposte_date
```

Registered through the REST API and editable directly from Gutenberg.

The displayed format reuses Humanity's date helper whenever available.

### External Link

Meta key:

```text
aif_riposte_external_url
```

When defined:

* the entire card becomes clickable
* the link opens in a new tab
* a "Learn more" label is displayed
* three animated chevrons reinforce the clickable behaviour
* the animation respects `prefers-reduced-motion`

---

## Content Ordering

Ordering relies on WordPress native:

```text
menu_order
```

Editors can reorder Ripostes via drag & drop in the administration.

The same ordering is preserved on the archive and during AJAX loading.

---

## AJAX Loading

The archive uses a **Load more** button.

Files involved:

```text
includes/ajax-load-more.php
assets/js/load-more.js
```

The plugin uses an **offset-based** loading strategy rather than classic pagination, ensuring the editorial grid remains consistent regardless of viewport size.

Current filters are also transmitted:

```text
qlocation
qriposte_theme
```

---

## Grid System

Cards follow an editorial layout that changes depending on screen size.

### Mobile

One card per row.

### Tablet

8-card repeating cycle.

### Desktop

10-card repeating cycle.

The layout relies on:

* a 6-column CSS grid
* dynamically computed layout classes
* automatic color assignment

Main function:

```php
aif_riposte_get_card_layout_classes()
```

The grid logic, CSS classes and AJAX loading are tightly coupled.

Any layout modification generally requires updating:

* `includes/card.php`
* `assets/css/aif-riposte.css`
* `assets/js/load-more.js`

---

## Card Content

Cards are rendered entirely from:

```text
includes/card.php
```

Cards may display:

* country (`location`)
* custom date
* editorial content
* optional external link
* "Learn more" CTA
* theme (`riposte_theme`)
* keyword (`riposte_tag`)

Images are forced to a square ratio through CSS.

Desktop layout:

* small cards: image above
* medium & large cards: image left / content right
* taxonomy chips displayed below the separator

---

## Fonts

Fonts are bundled with the plugin:

```text
assets/fonts/
```

Used fonts:

* Agency FB
* URW DIN

Font declarations are located in:

```text
assets/css/aif-riposte.css
```

---

## Colors

Card colors are **not** determined by content or taxonomy.

They depend solely on the card position inside the editorial grid.

Generated classes:

```text
aif-riposte-card--color-1
...
aif-riposte-card--color-5
```

Each variant defines:

* card background
* tag background
* country badge
* CTA accent color

---

## SEO

SEO customizations are implemented in:

```text
includes/seo.php
```

including:

* canonical URL
* SEO title
* Open Graph title
* Twitter title

Breadcrumb customization is handled separately by:

```text
includes/breadcrumb.php
```

Expected breadcrumb:

```text
Home > Get informed > Ripostes
```

---

## Assets

Assets are loaded centrally through:

```text
includes/assets.php
```

and include:

* CSS
* JavaScript
* Fonts

---

## Dependencies

The plugin depends on several Humanity theme components:

* `location` taxonomy
* `amnesty/archive-hero` pattern
* footer template part
* Humanity archive filters
* existing filter styles and scripts
* WordPress CSS variables provided by the theme
* Humanity date helper (when available)

---

## Development

### Adding a New Field

1. Register the meta in:

```text
includes/metaboxes.php
```

2. Add the Gutenberg UI:

```text
assets/js/editor.js
```

3. Render the value:

```text
includes/card.php
```

### Updating the Card Layout

Modify:

```text
includes/card.php
```

for:

* HTML structure
* displayed data
* CSS classes
* layout pattern
* color logic

### Updating the Grid

Main logic:

```php
aif_riposte_get_card_layout_classes()
```

Related files:

```text
includes/card.php
assets/css/aif-riposte.css
assets/js/load-more.js
```

### Updating Filters

Files to maintain together:

```text
templates/partials/archive-filters.php
includes/archive.php
includes/ajax-load-more.php
assets/js/load-more.js
```

### Updating AJAX Loading

Files:

```text
assets/js/load-more.js
includes/ajax-load-more.php
```

### Updating SEO

Files:

```text
includes/seo.php
includes/breadcrumb.php
```
