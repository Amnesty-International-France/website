# AIF RSS Importer

This project plugin imports external RSS entries as WordPress press releases.
It is intentionally small and depends on the `press-release` post type provided
by `humanity-theme`.

## Runtime Role

The plugin registers:

- the `aifrss_options` option, which stores import settings;
- the `aifrss_rss_import_event` WP-Cron hook;
- an admin settings page under `Settings > AIF RSS`;
- a manual admin action to run the import immediately.

On activation, the plugin creates default settings if they do not exist and
schedules the cron event. The current activation code falls back to the default
`daily` recurrence because it reads the cron hook name as an option key when it
looks for the configured frequency. Saving the settings page later reschedules
the event with the selected frequency.

On deactivation, it clears the scheduled hook. This is important because
WordPress keeps scheduled events until they are explicitly unscheduled.

## Configuration

The settings page controls:

- RSS feed URL, defaulting to `https://www.amnesty.org/fr/latest/feed/`;
- maximum number of entries fetched per run;
- import frequency: hourly, twice daily, daily, every three days, or every
  seven days;
- created post status: `publish` or `draft`.

When the frequency changes through the settings page, the plugin clears the
previous scheduled event and registers a new one.

## Import Behavior

The importer reads the configured feed with WordPress `fetch_feed()` and creates
posts of type `press-release`.

For each feed item, it:

- uses the item GUID, or permalink as fallback, as a stable import identifier;
- skips entries already stored with the same `aifrss_guid` post meta;
- copies the title and content, falling back to the description when content is
  empty;
- stores source metadata in `aifrss_guid`, `aifrss_source`, and
  `aifrss_pubdate`.

The import status is stored in WordPress options:

- `aifrss_last_run`;
- `aifrss_last_count`;
- `aifrss_last_error`.

## Operational Checks

Useful WP-CLI checks:

```bash
wp cron event list | grep aifrss_rss_import_event
wp option get aifrss_options --format=json
wp option get aifrss_last_error
```

To trigger the import outside the admin UI:

```bash
wp cron event run aifrss_rss_import_event
```

The manual import button is available from the settings page and requires the
`manage_options` capability and a valid WordPress nonce.

## Failure Modes

The importer returns without creating posts when:

- the `press-release` post type is not registered;
- no feed URL is configured;
- `fetch_feed()` returns a WordPress error.

If `wp_insert_post()` fails for one feed item, that item is skipped but no
specific import error is stored.

When changing this plugin, verify both the admin-triggered import and the
scheduled hook. The importer has no dedicated automated tests today.
