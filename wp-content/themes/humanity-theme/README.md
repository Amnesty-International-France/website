# Humanity Theme

This document describes the project-specific architecture of
`wp-content/themes/humanity-theme`.

## Role

`humanity-theme` is the main WordPress theme used by the Amnesty International
France website. It is derived from Amnesty International's upstream Humanity
theme, but it also contains a large amount of project-specific application code.

The theme is responsible for:

- public and authenticated page rendering;
- Full Site Editing templates, template parts, and PHP patterns;
- custom PHP-rendered Gutenberg blocks;
- custom post types and taxonomies;
- admin options and editor behavior;
- REST endpoints used by the frontend and editor;
- search customization;
- Salesforce integration for petitions, newsletters, users, and cases;
- local petition and urgent action persistence;
- `Mon Espace` routing, templates, access rules, and breadcrumbs;
- Cloudflare Turnstile integration for sensitive forms;
- integrations with Jetpack, Yoast SEO, The Events Calendar, WooCommerce, and
  MultilingualPress when available.

Because of that scope, the theme currently acts as both a presentation layer and
an application integration layer.

## Directory Layout

Important directories and files:

- `functions.php`: theme bootstrap and top-level module loading.
- `theme.json`: editor and design settings exposed to WordPress.
- `templates/`: Full Site Editing block templates.
- `parts/`: reusable Full Site Editing template parts.
- `patterns/`: PHP block patterns used by templates and content views.
- `partials/`: lower-level PHP view partials used by patterns/templates.
- `includes/`: theme setup, helpers, blocks, post types, taxonomies, REST API,
  business modules, and third-party plugin integrations.
- `commands/`: WP-CLI commands loaded only in CLI context.
- `assets/`: built scripts, styles, images, fonts, and source maps consumed by
  WordPress.
- `styles/`: style variations exposed through WordPress.
- `page-*.php`: classic PHP templates for specific pages, mostly authentication
  and `Mon Espace` flows.
- `tribe/` and `tribe-events/`: template overrides for The Events Calendar.

Generated or bulky directories such as `assets/` and `languages/` should not be
used as primary source-of-truth when changing theme behavior. The frontend
source of truth is `private/src`.

## Bootstrap

The theme bootstrap is centralized in `functions.php`.

The file loads modules in broad groups:

- root behavior: compatibility, caching, localization, accessibility, permalinks;
- helpers: archive, media, blocks, taxonomies, metadata, pagination, frontend
  utilities;
- admin and network options;
- theme setup: supports, navigation, body/head hooks, media, scripts/styles,
  analytics;
- KSES allowlists;
- PHP-rendered blocks and core block modifications;
- block pattern registration;
- post types and taxonomies;
- query filters;
- Salesforce connector;
- document, petition, and urgent action modules;
- search;
- REST API;
- RSS feed behavior;
- SEO;
- users;
- Jetpack;
- `Mon Espace`;
- optional WooCommerce and MultilingualPress integrations.

Most modules register themselves through WordPress hooks during load. This means
adding a module generally requires two steps:

1. create the implementation under the relevant `includes/*` directory;
2. add a `require_once` in the corresponding section of `functions.php`.

## Rendering Model

The theme uses a hybrid rendering model:

- Full Site Editing templates in `templates/*.html` define most page, archive,
  taxonomy, and single layouts.
- Template parts in `parts/*.html` provide reusable header, footer, and form
  fragments.
- PHP block patterns in `patterns/*.php` provide dynamic layout fragments and
  business-aware rendering.
- Classic PHP templates handle pages that need direct PHP control, especially
  login, password, account, and `Mon Espace` pages.
- `template-html-wrapper.php` bridges selected HTML templates into classic PHP
  template resolution.

For `Mon Espace`, `includes/my-space/template.php` applies a dedicated template
selection layer:

- if a specific `page-{slug}.php` exists, it is used;
- otherwise a specific `templates/page-{slug}.html` can be wrapped;
- otherwise `templates/page-my-space-default.html` is used as fallback for pages
  under `/mon-espace`.

This makes the authenticated area partly page-tree driven and partly
template-driven.

## Blocks And Patterns

PHP-rendered Gutenberg blocks live under `includes/blocks`. Each block usually
has a `register.php` file and, when dynamic rendering is needed, a `render.php`
file.

`includes/blocks/register.php` loads and registers the custom block set on
`init`. Examples include:

- cards and lists: article, chronicle, document, EDH, event, petition, training;
- layout blocks: section, carousel, slider, hero, call to action;
- campaign blocks: petition list, tweet action, urgent register form;
- navigation/content helpers: menu, term list, related posts, read more;
- homepage-specific blocks.

Core WordPress blocks are adjusted under `includes/core-blocks`. Those files
modify behavior or styles for selected core blocks such as buttons, images,
post content, query pagination, and social icons.

Block patterns live in `patterns/`. They are used as higher-level presentation
fragments for pages, archives, taxonomies, single content, sidebars, forms, and
`Mon Espace` views.

When adding rendering code:

- use a block when the editor needs a reusable editable unit;
- use a pattern when composing page or archive structure;
- use a partial when extracting reusable PHP markup;
- use a classic PHP template only when WordPress template resolution or
  request-specific control is required.

Detailed documentation: [Content authoring and API](./docs/content-authoring-and-api.md).

## Content Model

The theme registers multiple custom post types. Some use direct registration
functions; others use `Amnesty\Post_Type`, an abstract base class that supports
feature toggles and localized permalink settings.

Important post types include:

- `petition`;
- `document`;
- `newsletter`;
- `chronique`;
- `edh`;
- `training`;
- `press-release`;
- `local-structures`;
- `actualities-my-space`;
- `alert-banner`;
- `country`;
- `landmark`;
- `portrait`;
- `sidebar`;
- `pop-in`.

Taxonomies use a similar pattern through `Amnesty\Taxonomy`, which supports
feature toggles, localized slugs, REST metadata, custom templates, and admin
form adjustments.

Important taxonomies include:

- `category` as the content type taxonomy wrapper;
- `combat`;
- `location`;
- `keyword`;
- `landmark_category`;
- `document_type`;
- `document_democratic_type`;
- `document_instance_type`;
- `document_militant_type`.

Several default terms are seeded on `after_switch_theme` from
`includes/theme-setup/*`, for example categories, countries, combats, keywords,
and document classification terms.

Detailed documentation: [Content authoring and API](./docs/content-authoring-and-api.md).

## Business Tables

The theme creates and uses local business tables:

- `wp_aif_users`: local signer/user identity data;
- `wp_aif_petitions_signatures`: petition signatures and Salesforce sync state;
- `wp_aif_urgent_action`: urgent action registrations and Salesforce sync state.

Those tables are created with `dbDelta()` on `after_switch_theme`. The urgent
action schema also exposes a WP-CLI schema update command through
`wp update-db-schema`.

The schema is part of the theme today, even though it represents application
business data rather than presentation data. Any schema change should be handled
with an explicit version/update path, not only by changing the create-table SQL.

## Internal Business Modules

### Salesforce

Salesforce integration lives under `includes/salesforce`.

The module handles:

- client credentials authentication;
- token caching in WordPress options;
- generic GET/POST/PATCH helpers;
- petition creation and updates;
- petition signature Bulk API synchronization;
- user, newsletter, and case calls.

Required environment variables are read with `getenv()`, mainly:

- `AIF_SALESFORCE_URL`;
- `AIF_SALESFORCE_CLIENT_ID`;
- `AIF_SALESFORCE_SECRET`;
- several `AIF_SALESFORCE_CODES_*` values for origin and campaign mapping.

### Petitions

The petition module lives under `includes/petitions`.

It handles:

- local signer storage;
- local petition signature storage;
- Salesforce petition creation on `acf/save_post`;
- petition end-date updates in Salesforce;
- email existence checks through `humanity/v1/check-email`;
- WP-CLI synchronization of pending signatures to Salesforce.

Detailed documentation: [Petitions and urgent actions](./docs/petitions-urgent-actions.md).

### Urgent Actions

The urgent action module lives under `includes/urgent-action`.

It handles:

- local urgent action table creation;
- duplicate registration checks;
- unsynchronized action lookup;
- Salesforce synchronization through WP-CLI;
- schema update command used during production deployment.

Detailed documentation: [Petitions and urgent actions](./docs/petitions-urgent-actions.md).

### Documents

The document module includes REST search endpoints for private resource
documents and a WP-CLI command to report broken document links/files.

Document access and file handling logic is concentrated in
`includes/post-types/document.php`, which is currently one of the larger theme
modules.

### Search

Search customization lives under `includes/features/search`.

It provides:

- pretty search URLs;
- custom search query variables;
- taxonomy filters;
- title-only filtering in some contexts;
- SQL rewriting for richer filtered searches;
- dedicated search page/result helpers.

### `Mon Espace`

`Mon Espace` behavior lives under `includes/my-space` and the `page-*.php`
templates.

It handles:

- template resolution for pages under `/mon-espace`;
- authentication checks;
- member/non-member access restrictions;
- redirects for unauthorized pages;
- Yoast breadcrumb customization for authenticated-area contexts.

Detailed documentation: [Mon Espace](../../../docs/MON-ESPACE.md).

## REST API

The theme registers several REST endpoints. Important namespaces include:

- `amnesty/v1`: categories, menus, local structure search, geocode proxy;
- `humanity/v1`: email checks and document search endpoints;
- WordPress REST user endpoints extended through a custom users controller.

Some endpoints are public, while others require `is_user_logged_in`,
WordPress capabilities, or nonce validation. Permission handling should be
reviewed when adding endpoints, especially for routes that expose business data
or proxy external services.

Detailed documentation: [Content authoring and API](./docs/content-authoring-and-api.md).

## Assets

The theme does not own the frontend source files directly. Built assets are
stored under `wp-content/themes/humanity-theme/assets`, but source files live in
`private/src`.

Theme enqueueing happens mainly in
`includes/theme-setup/scripts-and-styles.php`:

- `bundle.css` and `bundle.js` are loaded on the frontend;
- `admin.css` and `admin.js` are loaded in wp-admin;
- `blocks.js` and `blocks.css` are loaded in the block editor;
- `editor.css` is loaded for editor block assets;
- `print.css` is loaded for printable post pages;
- localized data is passed to frontend and editor scripts through
  `wp_localize_script`.

The global frontend app is exposed as `window.App.default()` and is triggered by
an inline footer script registered by the theme. The inline script is also added
to the theme CSP hash filter.

The dedicated Turnstile asset is loaded from `assets/scripts/turnstile.js` when
`TURNSTILE_SITE_KEY` is configured.

## Third-Party Plugin Integrations

The theme contains integration code for optional or required plugins:

- Jetpack: contact form behavior, sitemap/search redirects, go-back messages.
- Yoast SEO: breadcrumbs, titles, canonical, Open Graph, primary term handling.
- The Events Calendar: taxonomy/meta box cleanup, venue API enrichment, template
  overrides.
- WooCommerce: loaded conditionally when WooCommerce is active, including cart,
  checkout, product, order, and template helpers.
- MultilingualPress: loaded conditionally for multilingual metadata, REST API,
  scheduled posts, and language selector behavior.
- CMB2/ACF: admin options, metaboxes, theme settings, and field definitions.

When touching integration code, verify that the plugin is available in the
target environment and that CI has stubs or plugin files for static analysis.

## Cloudflare Turnstile

Turnstile is integrated directly in the theme, not through the Cloudflare
WordPress plugin.

The theme:

- renders `.cf-turnstile` containers in specific forms/patterns;
- loads local `aif-turnstile` before Cloudflare's `api.js`;
- adds a preconnect hint for `https://challenges.cloudflare.com`;
- validates submitted tokens server-side through Cloudflare `siteverify`;
- maps Cloudflare error codes to user-facing French messages.

The corresponding frontend guard is sourced from `private/src/scripts` and built
into `assets/scripts/turnstile.js`.

## WP-CLI Commands

Theme commands are loaded only when `WP_CLI` is active.

Known commands include:

- `wp duplicate-countries`;
- `wp update-countries`;
- `wp sync` for petition signature synchronization;
- `wp sync` for urgent action synchronization;
- `wp table-urgent-action`;
- `wp update-db-schema`;
- `wp document-broken-list`.

There is command-name overlap around `wp sync` between petition and urgent
action synchronization. This should be reviewed before adding more WP-CLI
commands.

## Admin And Editor Behavior

Admin behavior is spread across `includes/admin`, CMB2/ACF field definitions,
theme option pages, and editor asset localization.

The theme provides:

- theme option pages for header, footer, social links, analytics, localization,
  news, and pop-ins;
- feature toggles for post types and taxonomies;
- permalink settings for localized slugs;
- block validation/display controls;
- user options;
- list table filters;
- local structure search tools;
- accessibility and general settings helpers.

Editor-facing settings are passed through `blocks.js` localization, including
available post types, taxonomy metadata, default sidebar settings, feature flags,
user roles, WordPress version, and optional WooCommerce data.

## Conventions For Changes

When changing theme behavior:

- prefer adding code to the relevant `includes/*` module instead of expanding
  `functions.php` inline;
- update `functions.php` only to wire a new module into the bootstrap;
- put frontend source changes in `private/src`, then rebuild theme assets;
- keep generated `assets/*` changes tied to their source changes;
- use existing post type and taxonomy base classes when adding configurable
  content structures;
- use existing helper functions before introducing new global helpers;
- treat business-table changes as migrations and update the related WP-CLI
  schema path when needed;
- check plugin availability before depending on third-party plugin symbols;
- keep public REST endpoints explicit about permissions and sanitization;
- avoid adding more unrelated business logic to the theme when a dedicated
  plugin boundary would be cleaner.

## Checks

Relevant checks for theme changes include:

- `composer run cs`;
- `composer run analyse -- --no-progress`;
- `composer lint`;
- `yarn lint:scripts` from `private`;
- `yarn lint:styles` from `private`;
- `yarn build:prod` from `private`;
- targeted tests such as `yarn test` when frontend behavior is touched.

The current CI covers PHP checks and GitHub Actions linting. Frontend checks are
available locally but are not yet part of the main CI workflow.
