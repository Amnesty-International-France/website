# Architecture

The Amnesty International France website is a WordPress application organized
around a custom theme, project-specific plugins, frontend build tooling,
workflow definitions, hosting support files, and several external integrations.

## Overview

The project is a full WordPress application. The repository versions WordPress
core, the main theme, several third-party plugins, project-specific plugins,
frontend build tooling, installation scripts, and deployment workflow
definitions.

The main building blocks are:

- [`wp-content/themes/humanity-theme`](../wp-content/themes/humanity-theme/README.md):
  the main site theme, derived from Amnesty International's Humanity theme and
  heavily adapted for Amnesty France needs.
- [`private`](../private/README.md): frontend sources, Webpack/Yarn
  configuration, JS/SCSS linting, frontend tests, and theme asset generation.
- [`wp-content/plugins/aif-donor-space`](../wp-content/plugins/aif-donor-space/README.md):
  business plugin for the donor space and part of the authenticated user area.
- [`wp-content/plugins/aif-rss-importer`](../wp-content/plugins/aif-rss-importer/README.md):
  RSS import plugin for press releases.
- [`wp-content/plugins/prismic-migration`](../wp-content/plugins/prismic-migration/README.md):
  Prismic migration plugin exposed through WP-CLI.
- [`wp-content/plugins/interactive-map`](../wp-content/plugins/interactive-map/README.md):
  custom Gutenberg block based on Leaflet.
- `clevercloud`, `infogerance`, `.github/workflows`: installation, hosting,
  backup, and deployment workflow support.

## WordPress Runtime

The site runs on WordPress, PHP, MySQL/MariaDB, and WP-CLI. Public pages,
archives, editorial pages, forms, and authenticated areas are rendered by
WordPress through a mix of block templates, PHP patterns, and classic PHP
templates.

The repository also contains several third-party plugins required by the site or
the back office:

- Advanced Custom Fields
- CMB2 and CMB2 extensions
- Jetpack
- The Events Calendar
- Yoast SEO
- Cloudflare

The theme and project plugins add custom post types, taxonomies, REST endpoints,
WP-CLI commands, and business tables on top of the standard WordPress data
model.

## Main Theme

The `humanity-theme` theme is the main integration point for the site. It
contains:

- Full Site Editing templates in `templates/*.html`;
- template parts in `parts/*.html`;
- PHP block patterns in `patterns/*.php`;
- specific PHP templates for login and `Mon Espace` pages;
- PHP-rendered blocks in `includes/blocks`;
- post type, taxonomy, REST API, SEO, search, Salesforce, petition, urgent
  action, and plugin integration code.

Assets consumed by the theme are generated into
`wp-content/themes/humanity-theme/assets` from the `private` source tree.

## Frontend And Assets

The `private` directory contains the theme frontend toolchain:

- Webpack to compile scripts and styles;
- Yarn for dependency management;
- Sass/PostCSS for styles;
- ESLint and Stylelint for frontend quality checks;
- Vitest for frontend tests;
- TypeScript in targeted `checkJs` mode for selected JavaScript modules.

The main Webpack entries are:

- `bundle`: global frontend application for the theme;
- `turnstile`: dedicated client guard for forms protected by Turnstile;
- `blocks`: Gutenberg editor/block code;
- `editor`: editor styles and behavior;
- `admin`: back-office assets.

The build writes directly to versioned theme assets:

- `wp-content/themes/humanity-theme/assets/scripts`
- `wp-content/themes/humanity-theme/assets/styles`
- `wp-content/themes/humanity-theme/assets/images`

## Custom Plugins

### [`aif-donor-space`](../wp-content/plugins/aif-donor-space/README.md)

Business plugin for the donor space and part of the authenticated user area. It
creates or ensures the presence of critical pages, loads its own assets, and
organizes the domain around:

- user authentication;
- two-factor authentication;
- bank details;
- contact preferences;
- tax receipts;
- Salesforce calls;
- email delivery through Mailgun.

### [`aif-rss-importer`](../wp-content/plugins/aif-rss-importer/README.md)

RSS import plugin configurable from the WordPress admin. It imports external
content into the `press-release` post type and schedules imports through
WP-Cron.

### [`prismic-migration`](../wp-content/plugins/prismic-migration/README.md)

Tooling plugin for migrating Prismic content into WordPress. It exposes WP-CLI
commands, including migration by content type and post-import link repair.

### [`interactive-map`](../wp-content/plugins/interactive-map/README.md)

Custom Gutenberg block plugin. It has its own build based on `@wordpress/scripts`
and uses Leaflet for map rendering.

## Business Data

In addition to standard WordPress tables, the project creates specific business
tables:

- `wp_aif_users`: local signer/business user data;
- `wp_aif_petitions_signatures`: petition signatures and Salesforce sync state;
- `wp_aif_urgent_action`: urgent action registrations and sync state.

Editorial and business content is structured with several custom post types and
taxonomies, including:

- post types: `petition`, `document`, `newsletter`, `chronique`, `edh`,
  `training`, `press-release`, `local-structures`, `sidebar`, `pop-in`;
- taxonomies: `combat`, `location`, `keyword`, `document_type`,
  `document_democratic_type`, `document_instance_type`,
  `document_militant_type`.

## External Integrations

The project communicates with several external services:

- [Salesforce](./SALESFORCE.md): OAuth client credentials, REST API, and Bulk
  API for petitions, users, newsletters, cases, and urgent actions;
- Mailgun: email delivery for the donor space and verification flows;
- [Cloudflare Turnstile](./TURNSTILE.md): security verification for sensitive
  forms;
- Prismic: historical content source used during migration;
- Amnesty.org RSS: source for imported press releases;
- Google Geocoding: local structure search/geocoding;
- Google Tag Manager, Google Analytics, Hotjar, and VWO: analytics and tracking.

Baseline environment variables are documented in `.env.example`, with
additional integration-specific variables described in the relevant subsystem
documents and visible from the `getenv()` calls in the application code.

## Forms And Security

Several public and authenticated forms interact with Salesforce, Mailgun, local
tables, and Turnstile. Forms protected by Turnstile use a custom theme
integration:

- HTML widget rendering in the relevant templates/patterns;
- local `turnstile.js` script loaded before the Cloudflare API;
- server-side validation through `siteverify`;
- error messages adapted to the invisible Turnstile mode.

Sensitive REST endpoints rely on WordPress permissions, REST nonces, or the
current authentication state depending on the use case.

## Environments

The project supports several local and remote environments, mapped in
[`ENVIRONMENTS.md`](./ENVIRONMENTS.md):

- historical installation through Castor and WP-CLI;
- local WordPress environment through `private/.wp-env.json`;
- local MySQL/MariaDB database;
- `.env`-based configuration;
- Infomaniak preprod and production hosts;
- historical Clever Cloud support.

The Clever Cloud file `infogerance/aif-clever-cloud.php` generates a WordPress
configuration from platform environment variables.

Detailed GitHub Actions and Infomaniak remote-script deployment behavior is
documented in [`DEPLOYMENT.md`](./DEPLOYMENT.md).

## Quality And CI

Available checks are:

- PHP-CS-Fixer through `composer run cs`;
- PHPStan through `composer run analyse`;
- PHPCS through `composer lint`;
- Actionlint for GitHub workflows;
- ESLint through `yarn lint:scripts`;
- Stylelint through `yarn lint:styles`;
- Webpack through `yarn build` or `yarn build:prod`;
- Vitest through `yarn test`;
- targeted Turnstile typecheck through `yarn typecheck:turnstile`.

The current GitHub CI runs PHP checks, frontend Turnstile build/unit/type/E2E
checks, and GitHub Actions linting.

## Documentation Map

This file should remain a high-level overview. Subsystem documentation should
live next to the code it describes when possible. Current subsystem documents:

- [`wp-content/themes/humanity-theme`](../wp-content/themes/humanity-theme/README.md):
  theme, templates, patterns, blocks, hooks, post types, taxonomies, and
  integrations;
- [`wp-content/themes/humanity-theme/docs/content-authoring-and-api.md`](../wp-content/themes/humanity-theme/docs/content-authoring-and-api.md):
  content model, blocks, patterns, REST endpoints, and authoring surface;
- [`private`](../private/README.md): frontend build, assets, JS/SCSS
  conventions, tests, and typecheck;
- [`wp-content/plugins/aif-donor-space`](../wp-content/plugins/aif-donor-space/README.md):
  donor space, pages, data, Salesforce, Mailgun, and REST API;
- [`wp-content/plugins/aif-rss-importer`](../wp-content/plugins/aif-rss-importer/README.md):
  RSS import settings, WP-Cron scheduling, and imported press-release metadata;
- [`wp-content/plugins/interactive-map`](../wp-content/plugins/interactive-map/README.md):
  Leaflet Gutenberg block, REST endpoint dependencies, and build workflow;
- [`wp-content/plugins/prismic-migration`](../wp-content/plugins/prismic-migration/README.md):
  Prismic WP-CLI migration commands, transformers, and link repair;
- [`docs/SALESFORCE.md`](./SALESFORCE.md): Salesforce authentication, REST
  helpers, and Bulk API synchronization;
- [`docs/TURNSTILE.md`](./TURNSTILE.md): Cloudflare Turnstile runtime flow,
  local dummy keys, and E2E coverage;
- [`docs/MON-ESPACE.md`](./MON-ESPACE.md): authenticated donor/member area,
  theme/plugin boundary, redirects, and weak points;
- [`docs/ENVIRONMENTS.md`](./ENVIRONMENTS.md): local, test, staging,
  production, and historical environment map;
- [`docs/DEPLOYMENT.md`](./DEPLOYMENT.md): GitHub Actions deployment flows,
  remote scripts, operational checks, and deployment weak points.
