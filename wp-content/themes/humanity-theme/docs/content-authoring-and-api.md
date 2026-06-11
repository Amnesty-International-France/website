# Content Authoring And API

The theme defines most editorial building blocks for the site: content types,
taxonomies, PHP-rendered blocks, PHP patterns, and REST endpoints used by the
frontend and editor.

## Role

This subsystem gives editors structured content and reusable layout tools while
also exposing selected data to JavaScript through REST endpoints.

The theme owns this surface today. Source behavior lives in PHP under
`includes/`, while editor-side JavaScript and styles are built from
`private/src`.

## Main Flows

Content types and taxonomies are loaded from `functions.php` during theme
bootstrap. Some post types use direct registration functions, while others use
the shared `Amnesty\Post_Type` abstraction. Taxonomies use the matching
`Amnesty\Taxonomy` abstraction when they need localized slugs, REST metadata, or
custom templates.

PHP-rendered Gutenberg blocks live under `includes/blocks`. Each block usually
has a `register.php` file and, when needed, a `render.php` file. The central
`includes/blocks/register.php` file loads them and registers the block set on
`init`.

Patterns under `patterns/` compose higher-level page, archive, single, sidebar,
form, and `Mon Espace` fragments. Use patterns for layout composition and
partials for reusable lower-level markup.

REST endpoints live under `includes/rest-api`, feature-specific directories, or
plugin modules when they belong to a plugin. Permission handling varies by
route, so check it before exposing business data or proxying external services.

## Data Ownership

Editorial data stays in WordPress posts, terms, post meta, and options.
Business tables are documented separately because they require stronger
migration discipline.

Generated assets under `assets/` are runtime output. Do not treat them as the
source of frontend or editor behavior; use `private/src` for source changes.

## External Integrations

This subsystem touches:

- WordPress REST API and block editor APIs;
- ACF and CMB2 field definitions;
- Yoast SEO metadata and breadcrumb behavior;
- The Events Calendar, WooCommerce, Jetpack, and MultilingualPress integration
  points when those plugins are active;
- frontend build artifacts from `private`.

## Operational Commands

Relevant checks depend on the changed layer:

```bash
composer run cs
composer run analyse -- --no-progress --error-format=raw
cd private && yarn lint
cd private && yarn build
```

When changing routes, post type slugs, taxonomy slugs, or custom rewrite rules,
flush rewrite rules in the target environment.

When changing built editor or frontend assets, rebuild the relevant Webpack
entry and commit generated theme assets with the matching source changes.

## Known Weak Points

- The theme combines presentation, content model, REST, and integration logic.
- Many editor-facing blocks are PHP-rendered and registered manually; missing a
  `require_once` or registration call can silently remove an editor feature.
- REST endpoints are distributed across multiple directories, so permission
  behavior must be checked route by route.
- Some taxonomy and post type behavior depends on custom abstractions while
  other types use direct registration functions.
- Generated assets are versioned, which makes source/build drift possible when
  source changes are committed without the corresponding build output.
- Slug, rewrite, or template changes can affect public URLs, `Mon Espace`, SEO,
  and cached links.

## Code Map

Start here when changing this surface:

- `functions.php`: top-level module loading order.
- `includes/post-types/`: custom post types and post-type abstractions.
- `includes/taxonomies/`: taxonomy abstractions, filters, and metadata.
- `includes/theme-setup/`: seeded terms, rewrite setup, supports, media,
  scripts, and styles.
- `includes/blocks/register.php`: PHP-rendered block loading and registration.
- `includes/blocks/*/register.php` and `render.php`: individual block contracts.
- `patterns/`, `partials/`, `templates/`, and `parts/`: rendering composition.
- `includes/rest-api/`: central REST controllers and field extensions.
- `private/src/scripts/blocks.js`, `editor.js`, and `private/src/styles/`:
  editor-side source assets.

## Change Checklist

Before shipping changes in this area:

- verify the relevant post type, taxonomy, block, pattern, or endpoint still
  registers;
- check the editor when changing block or editor assets;
- check frontend rendering when changing templates, patterns, or partials;
- review REST permissions when adding or changing an endpoint;
- flush rewrite rules for slug or route changes;
- rebuild frontend/editor assets when `private/src` changes.
