# ADR - O1 - Choix technique pour l'espace don

## Pourquoi un plugin et pas un sous-thème ?

L'espace don est conçu comme un plugin WordPress et non comme un sous-thème. Le sous-thème était la première piste initialement choisie. Durant le développement, nous avons constaté que le thème de branding Amnesty (qui inclut les éléments UI d'Amnesty) n'était pas utilisable avec un sous-thème. Par exemple, il n'était pas possible d'avoir le "bouton jaune d'Amnesty".

Aussi, nous avons constaté qu'un plugin permettrait de répondre mieux à nos besoins qu'un sous-thème.

### Les besoins

D'un point de vue technique, nous avions besoin que :

1. Les URL utilisées dans l'espace don soient prédictives. C'est-à-dire que l'on puisse déterminer à l'avance quelles seront les URL des différentes pages pour pouvoir effectuer des liens entre les pages2.
2. Toutes les pages de l'espace dont puissent facilement être mis en ligne sur les différents environnements en limitant les actions manuelles
3. Rester le plus proche possible du Thème Humanity en limitant 
4.  L'espace don puisse s'intégrer simplement dans l'arborescence du site global, mais aussi pouvoir vivre de façon "standalone" pendant la construction du site.


### Réponses aux besoins

#### 1. URL prédictives

Pour répondre à ce besoin, nous avons configuré le plugin pour qu'il recrée l'arborescence de l'espace don à chaque activation du plugin.
Cela permet de s'assurer qu'activation après activation, les pages nécessaires au fonctionnement de l'espace don soient présentes.

#### 2. Limiter les actions manuelles

Dans un sous-thème, il est possible de créer des templates de pages. Une fois créées il sera nécessaire de créer une page (généralement via l'interface d'administration) et de lier cette page au template créé.

Il nous semblait important d'éviter de refaire ces actions manuelles sur chaque environnement.

L'approche "plugin" permet de créer l'arborescence automatiquement en liant chaque page à son template
 

#### 3. Rester au plus proche du Thème Humanity

Ce besoin a été difficile à répondre. Nous en reparlerons plus bas.

## Intégration dans le site global et version "standalone"

Pour répondre à ce besoin, nous avons décider que chaque URL de l'espace don serait une "sous-route" préfixée par `/espace-don`

## Structure du plugin

Arborescence du plugin "Espace Don" au 20/02/25

``
├── assets
│   ├── css // contient le fichier "style.css" reprenant tout le CSS "custom" de l'espace don
│   └── js // contient le JS de l'espace don
├── includes
│   ├── domain // contient toutes les fonctions "Métiers" de l'espace don
│   │   ├── 2FA // contient tout ce qui concerne l'authentification à deux facteurs
│   │   ├── bank // contient toutes les fonctions ce qui concerne les informations bancaires des utilisateurs
│   │   ├── contact  // contient toutes les fonctions qui concernent les moyens qu'a un utilisateur pour communiquer avec Amnesty
│   │   └── tax-receipt // contient tout ce qui concerne les fonctions nécessaire à la gestion des reçus fiscaux
│   └── sales-force // contient toutes les fonctions utilitaires pour interagir avec Salesforce
└── templates // contient tous les templates associés à une page
    └── partials // contient les "partials" (composants UI réutilisables sur les pages)
``

## Gestion des CSS

La première approche était de rester au plus proche du thème Humanity. 

Cependant, nous avons constaté que :

- la [documentation du thème](https://wordpresstheme.amnesty.org/) n'était pas complète. Elle est orientée "utilisateur voulant utilisé le thème" mais n'est pas destinée à des profils développeurs. Par exemple, il n'y pas d'indication sur comment créer certains éléments UI comme le fait par exemple [Bootsrap](https://getbootstrap.com/docs/5.3/components/badge/)
- Amnesty France souhaitait avoir une identité graphique un peu différente d'Amnesty International
- nos designer n'était pas satisfait du rendu de certaines pages uniquement construites avec le CSS incus dans le thème.

De ce fait, nous avons introduit du CSS "Custom" pour l'espace don.

Celui-ci est contenu dans le fichier "assets/css/styles.css"

### Quelques principes

Chaque class est préfixée par `aif` pour limiter le risque de collision

Au démarrage, en pensant que le CSS allait rester limité, nous avons eu une approche à la [tailwind](https://tailwindcss.com/) en créant des classes de CSS "utilitaires" (`.aif-mt1w` pour rajouter du margin-top, ...).

Certains éléments UI devaient être construits (car n'existant pas dans le thème) et cette approche a montrer ces limites. Une partie du CSS utilise donc la nomenclature ["Block Element Modifier" (BEM)](https://getbem.com/)

### Comment est inclus le CSS dans le plugin ?

Les styles sont ajoutés dans le plugin via la fonction `aif_donor_space_enqueue_assets` qui se trouve dans le fichier `aif-donor-space.php`.

Cette fonction utilise la fonction Wordpress [wp_enqueue_style()](https://developer.wordpress.org/reference/functions/wp_enqueue_style/)
