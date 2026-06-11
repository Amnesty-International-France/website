# Prismic Migration

This project plugin exposes WP-CLI commands used to migrate the historical
Amnesty France Prismic repository into WordPress with `humanity-theme`.

The plugin loads its migration classes only when `WP_CLI` is active. It is not
a public runtime feature.

## Commands

The plugin registers two commands:

- `wp prismic-migration`: fetches Prismic documents, transforms them into
  WordPress posts and blocks, assigns taxonomy terms, uploads featured media,
  and stores SEO/Open Graph metadata;
- `wp repair-links`: replaces placeholders left by migration transforms in post
  content and selected related-post metadata.

The Prismic API endpoint is hardcoded in `PrismicFetcher.php` as
`https://amnestyfr.cdn.prismic.io/api/v2`. The fetcher calls Prismic Content
API V2 directly: it first reads the repository ref, then queries
`/documents/search` with pagination, type, date, ordering, or document ID
filters.

## Migration Usage

Content types are defined in [`Type.php`](Type.php).

```bash
wp prismic-migration --type=news
```

Common options:

- `--limit=<value>`: limit the number of fetched documents, default `-1`;
- `--type=<type>`: import a Prismic content type;
- `--ordering=ASC|DESC`: order by `last_publication_date`, default `DESC`;
- `--dry-run`: parse and count documents without inserting or updating posts;
- `--id=<value>`: import a single Prismic document by ID;
- `--force`: replace existing post content when a matching post already exists;
- `--since=<YYYY-MM-DD>`: fetch documents after a minimum publication date.

Examples:

```bash
wp prismic-migration --type=news --dry-run
wp prismic-migration --type=news --limit=100 --ordering=ASC
wp prismic-migration --type=news --since=2025-11-01
wp prismic-migration --type=news --force
wp prismic-migration --id=ABCD12345
```

The command checks existing content by slug and mapped WordPress post type. When
`--force` is not set, existing posts are skipped. When `--force` is set, the
command waits 5 seconds before continuing and updates matching posts; it does
not remove existing media.

Invalid dates passed to `--since` can fail during `DateTime` construction.

## Supported Content Types

The current migration layer includes transformers for content such as:

- `action`
- `actionmobilisation`
- `articlechronique`
- `communiquePresse`
- `dossier`
- `edh`
- `evenement`
- `index`
- `news`
- `page`
- `pays`
- `petition`
- `portrait`
- `rapport`
- `soutien`
- `structureMilitante`

Use `Type.php` as the source of truth for accepted values and WordPress post
type mapping. If Prismic returns an unsupported document type, the command logs
a warning but does not currently skip safely before asking the transformer
factory for a transformer, so that case can abort the run.

## Transformation Pipeline

The migration command coordinates:

- `PrismicFetcher`: pagination, ordering, date filtering, and single-document
  fetches from Prismic;
- `DocTransformerFactory` and `transformers/*`: mapping Prismic document types
  into WordPress post arrays and block content;
- `blocks/*`: mapping Prismic slices into WordPress blocks;
- `TaxMapper`: taxonomy term mapping;
- `FileUploader`: media sideloading and featured image setup;
- `LinksUtils`: temporary link placeholders that can be repaired after import.

Block content is serialized with `serialize_blocks()` before insertion.

Media upload deduplicates existing attachments by generated title. JP2 images
are converted to JPEG through `Imagick`, and filenames are normalized through
`transliterator_transliterate`, so the runtime PHP environment must provide the
needed extensions for those paths.

## Repair Links

Run this after imports that created placeholder links:

```bash
wp repair-links
```

The command scans published and private posts, updates post content, and repairs
the `_related_posts_selected` meta field when it contains placeholder values.

## Operational Notes

- Run `--dry-run` first when changing transformers or importing a new type.
- Use `--force` only when replacing already migrated content is intentional.
- The command performs remote HTTP requests and media uploads; run it from an
  environment with network access and a writable uploads directory.
- Prefer a production-like WP-CLI context, because the commands are registered
  with `@when after_wp_load` and rely on WordPress post types, taxonomies, media
  functions, and theme/plugin code being loaded.
- There are no dedicated automated tests for the migration plugin today.
