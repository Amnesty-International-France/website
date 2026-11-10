<?php

declare(strict_types=1);

/**
 * Report the events the Prismic migration imported several times.
 *
 * The import left many events with broken UTC meta (an _EventEndDateUTC
 * before _EventStartDateUTC) or none at all, so TEC refused to create their
 * occurrence. The migration only checked whether an event existed through a
 * WP_Query on its slug, and The Events Calendar joins every tribe_events
 * query on its occurrences table: such an event is invisible to that check
 * (and answers 404) while its slug stays taken in wp_posts. Each new run
 * created a copy, which wp_unique_post_slug() suffixed -2, then -3.
 *
 * Duplicates are matched on their content, never on their slug: editors also
 * suffix yearly editions by hand (foire-aux-livres-mably1, -mably2), and those
 * have different dates and must be kept.
 *
 * The oldest event of a group is kept: usually the original, whose URL has no
 * suffix but answers 404 for lack of an occurrence. --repair rebuilds that
 * occurrence through TEC, which brings the original URL back online. Copies
 * are only listed so far, never removed.
 *
 * Usage:
 *   wp amnesty dedupe-events
 *   wp amnesty dedupe-events --format=csv > doublons.csv
 *   wp amnesty dedupe-events --repair
 */

use TEC\Events\Custom_Tables\V1\Updates\Events;

if (! function_exists('amnesty_dedupe_events_normalise_text')) {
    /**
     * Reduce a text to what a reader sees: no markup, entities decoded,
     * whitespace collapsed. Two imports of the same Prismic document can differ
     * in markup only.
     *
     * @param string $text the raw text or HTML
     *
     * @return string
     */
    function amnesty_dedupe_events_normalise_text(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

if (! function_exists('amnesty_dedupe_events_fingerprint')) {
    /**
     * Content key shared by the copies of one imported event.
     *
     * The venue is compared by name: the migration matched venues on their
     * exact, published title only, so two copies may point at two venue posts
     * with the same name.
     *
     * @param object $event a row from amnesty_dedupe_events_fetch()
     *
     * @return string
     */
    function amnesty_dedupe_events_fingerprint(object $event): string
    {
        return md5(implode("\0", [
            amnesty_dedupe_events_normalise_text((string) $event->post_title),
            (string) $event->start_date,
            (string) $event->end_date,
            amnesty_dedupe_events_normalise_text((string) $event->venue),
            amnesty_dedupe_events_normalise_text((string) $event->post_content),
        ]));
    }
}

if (! function_exists('amnesty_dedupe_events_group')) {
    /**
     * Group events sharing a fingerprint, dropping the ones with no copy.
     *
     * @param array<int,object> $events rows from amnesty_dedupe_events_fetch()
     *
     * @return array<string,array<int,object>> groups of at least two events, by fingerprint
     */
    function amnesty_dedupe_events_group(array $events): array
    {
        $groups = [];

        foreach ($events as $event) {
            $groups[amnesty_dedupe_events_fingerprint($event)][] = $event;
        }

        return array_filter($groups, fn (array $group) => count($group) > 1);
    }
}

if (! function_exists('amnesty_dedupe_events_pick_keeper')) {
    /**
     * Choose the copy to keep in a group of duplicates.
     *
     * Each event carries ID, post_name, post_date, post_modified, post_status
     * and occurrences (rows in the TEC occurrences table; 0 means the event
     * answers 404 and is invisible to WP_Query).
     *
     * The oldest one is kept even without an occurrence, as agreed on
     * MAINT-323 (https://linear.app/les-tilleuls/issue/MAINT-323/seo-pages-orphelines-et-url-canoniques#comment-7bfbfb67):
     * it holds the original URL, and amnesty_dedupe_events_repair() brings it
     * back online.
     *
     * The oldest is the lowest ID, not the earliest post_date: the migration
     * copied the Prismic date into post_date, so a copy can carry an older
     * date than its original (soiree-debat-a-pechbonnieu-2 is dated
     * 2016-10-07, the original 2016-10-19).
     *
     * @param array<int,object> $group at least two duplicates, in no particular order
     *
     * @return object the event to keep, one of $group
     */
    function amnesty_dedupe_events_pick_keeper(array $group): object
    {
        usort($group, static function ($a, $b) {
            return (int) $a->ID <=> (int) $b->ID;
        });
        return $group[0];
    }
}

if (! function_exists('amnesty_dedupe_events_needs_repair')) {
    /**
     * Whether an event lacks its TEC occurrence, hence answers 404.
     *
     * NULL means the occurrences table does not exist: nothing to repair.
     *
     * @param object $event a row from amnesty_dedupe_events_fetch()
     *
     * @return bool
     */
    function amnesty_dedupe_events_needs_repair(object $event): bool
    {
        return '0' === (string) $event->occurrences;
    }
}

if (! function_exists('amnesty_dedupe_events_to_repair')) {
    /**
     * Events whose occurrence --repair must rebuild: every event without one,
     * except the copies of a group, which would go online next to their
     * original.
     *
     * @param array<int,object>               $events rows from amnesty_dedupe_events_fetch()
     * @param array<string,array<int,object>> $groups result of amnesty_dedupe_events_group()
     *
     * @return array<int,object>
     */
    function amnesty_dedupe_events_to_repair(array $events, array $groups): array
    {
        $copies = [];

        foreach ($groups as $group) {
            $keeper = amnesty_dedupe_events_pick_keeper($group);

            foreach ($group as $event) {
                if ($event->ID !== $keeper->ID) {
                    $copies[$event->ID] = true;
                }
            }
        }

        return array_values(array_filter(
            $events,
            fn (object $event) => ! isset($copies[$event->ID]) && amnesty_dedupe_events_needs_repair($event)
        ));
    }
}

if (! function_exists('amnesty_dedupe_events_utc_meta')) {
    /**
     * Compute the UTC meta of an event from its local dates, as TEC does when
     * an event is saved in the admin.
     *
     * @param string       $start    local start date, Y-m-d H:i:s
     * @param string       $end      local end date, Y-m-d H:i:s
     * @param DateTimeZone $timezone timezone of the local dates
     *
     * An empty or malformed date gives null: DateTimeImmutable would read the
     * first as "now" and throw on the second.
     *
     * @return array{_EventStartDateUTC: string, _EventEndDateUTC: string, _EventDuration: int}|null
     */
    function amnesty_dedupe_events_utc_meta(string $start, string $end, DateTimeZone $timezone): ?array
    {
        $start_date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $start, $timezone);
        $end_date   = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $end, $timezone);

        if (! $start_date || ! $end_date || $start_date->format('Y-m-d H:i:s') !== $start || $end_date->format('Y-m-d H:i:s') !== $end) {
            return null;
        }

        $utc        = new DateTimeZone('UTC');
        $start_date = $start_date->setTimezone($utc);
        $end_date   = $end_date->setTimezone($utc);

        return [
            '_EventStartDateUTC' => $start_date->format('Y-m-d H:i:s'),
            '_EventEndDateUTC'   => $end_date->format('Y-m-d H:i:s'),
            '_EventDuration'     => $end_date->getTimestamp() - $start_date->getTimestamp(),
        ];
    }
}

if (! function_exists('amnesty_dedupe_events_repair')) {
    /**
     * Rebuild the TEC occurrence of an event from its post meta.
     *
     * Updates\Events::update() is what TEC runs when an event is saved in the
     * admin: it upserts the wp_tec_events row, then regenerates the
     * occurrences. It reads the UTC dates and the duration from the post meta
     * as they are, and returns false, without throwing, when they give a
     * negative duration. The import left such meta (an _EventEndDateUTC before
     * _EventStartDateUTC) or none at all, so they are first rebuilt from the
     * local dates and the event timezone. An event without _EventTimezone gets
     * the site one, saved as its timezone, since TEC refuses an empty one.
     *
     * When the local dates are empty or malformed, nothing is written. When
     * TEC still creates no occurrence, the previous meta are put back.
     *
     * @global wpdb $wpdb
     *
     * @param object $event a row from amnesty_dedupe_events_fetch()
     *
     * @return bool whether the event has an occurrence afterwards
     */
    function amnesty_dedupe_events_repair(object $event): bool
    {
        global $wpdb;

        if (! function_exists('tribe') || ! class_exists(Events::class) || ! class_exists(Tribe__Timezones::class)) {
            return false;
        }

        $post_id  = (int) $event->ID;
        $stored   = get_post_meta($post_id, '_EventTimezone', true);
        $timezone = Tribe__Timezones::build_timezone_object($stored ?: null);
        $utc_meta = amnesty_dedupe_events_utc_meta((string) $event->start_date, (string) $event->end_date, $timezone);

        if (null === $utc_meta) {
            return false;
        }

        // TEC reads _EventTimezone as is and rejects an empty one.
        if (! $stored) {
            $utc_meta['_EventTimezone'] = $timezone->getName();
        }

        $previous = [];

        foreach ($utc_meta as $key => $value) {
            $previous[$key] = metadata_exists('post', $post_id, $key) ? get_post_meta($post_id, $key, true) : null;
            update_post_meta($post_id, $key, $value);
        }

        tribe(Events::class)->update($post_id);

        $occurrences = $wpdb->prefix . 'tec_occurrences';

        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $occurrences WHERE post_id = %d", $post_id)) > 0) {
            return true;
        }

        foreach ($previous as $key => $value) {
            null === $value ? delete_post_meta($post_id, $key) : update_post_meta($post_id, $key, $value);
        }

        return false;
    }
}

if (! function_exists('amnesty_dedupe_events_fetch')) {
    /**
     * Load every published or private event straight from the tables.
     *
     * WP_Query is avoided on purpose: TEC would hide the events that have no
     * occurrence, which are precisely the originals left behind.
     *
     * An event may have several _EventVenueID: their names are joined in a
     * subquery, so that each event stays on one row and cannot become its own
     * duplicate.
     *
     * @global wpdb $wpdb
     *
     * @return array<int,object>
     */
    function amnesty_dedupe_events_fetch(): array
    {
        global $wpdb;

        $occurrences = $wpdb->prefix . 'tec_occurrences';
        $has_table   = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $occurrences)) === $occurrences;

        $occurrence_count = $has_table
            ? "(SELECT COUNT(*) FROM {$occurrences} o WHERE o.post_id = p.ID)"
            : 'NULL';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results(
            "SELECT p.ID, p.post_name, p.post_title, p.post_content, p.post_status, p.post_date, p.post_modified,
                start_meta.meta_value AS start_date,
                end_meta.meta_value AS end_date,
                (SELECT GROUP_CONCAT(venue.post_title ORDER BY venue.post_title SEPARATOR ' / ')
                    FROM {$wpdb->postmeta} venue_meta
                    JOIN {$wpdb->posts} venue ON venue.ID = venue_meta.meta_value
                    WHERE venue_meta.post_id = p.ID AND venue_meta.meta_key = '_EventVenueID') AS venue,
                {$occurrence_count} AS occurrences
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} start_meta ON start_meta.post_id = p.ID AND start_meta.meta_key = '_EventStartDate'
            LEFT JOIN {$wpdb->postmeta} end_meta ON end_meta.post_id = p.ID AND end_meta.meta_key = '_EventEndDate'
            WHERE p.post_type = 'tribe_events' AND p.post_status IN ('publish', 'private')
            ORDER BY p.ID"
        );
    }
}

if (! function_exists('amnesty_dedupe_events_command')) {
    /**
     * WP-CLI entrypoint.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format of the duplicates list.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     * ---
     *
     * [--repair]
     * : Rebuild the missing occurrence of every event, copies excepted. Nothing is removed.
     *
     * @param array $args       positional arguments (unused)
     * @param array $assoc_args associative arguments
     *
     * @return void
     */
    function amnesty_dedupe_events_command($args, $assoc_args): void
    {
        $events  = amnesty_dedupe_events_fetch();
        $groups  = amnesty_dedupe_events_group($events);
        $rows    = [];
        $to_fix  = amnesty_dedupe_events_to_repair($events, $groups);

        foreach ($groups as $fingerprint => $group) {
            $keeper = amnesty_dedupe_events_pick_keeper($group);

            foreach ($group as $event) {
                $is_keeper = $event->ID === $keeper->ID;

                $rows[] = [
                    'groupe'      => substr($fingerprint, 0, 8),
                    'action'      => $is_keeper ? 'garder' : 'supprimer',
                    'reparer'     => $is_keeper && amnesty_dedupe_events_needs_repair($event) ? 'oui' : '',
                    'ID'          => $event->ID,
                    'slug'        => $event->post_name,
                    'statut'      => $event->post_status,
                    'occurrences' => $event->occurrences ?? 'n/a',
                    'publie'      => $event->post_date,
                    'modifie'     => $event->post_modified,
                    'debut'       => $event->start_date,
                    'titre'       => $event->post_title,
                ];
            }
        }

        $format = $assoc_args['format'] ?? 'table';

        if ($rows) {
            WP_CLI\Utils\format_items($format, $rows, array_keys($rows[0]));
        }

        if ('table' === $format) {
            $orphans = count(array_filter($events, 'amnesty_dedupe_events_needs_repair'));

            WP_CLI::success(sprintf(
                '%d événements inspectés, %d groupes de doublons, %d copies en trop, %d événements à réparer. %d événements sans occurrence (404) au total.',
                count($events),
                count($groups),
                count($rows) - count($groups),
                count($to_fix),
                $orphans
            ));
        }

        if (! isset($assoc_args['repair'])) {
            return;
        }

        $failed = 0;

        foreach ($to_fix as $event) {
            if (amnesty_dedupe_events_repair($event)) {
                WP_CLI::log(sprintf('event %d (%s): occurrence recréée', $event->ID, $event->post_name));

                continue;
            }

            WP_CLI::warning(sprintf('event %d (%s): réparation impossible, ses éventuelles copies doivent être conservées', $event->ID, $event->post_name));
            $failed++;
        }

        WP_CLI::success(sprintf('%d événements réparés, %d échecs.', count($to_fix) - $failed, $failed));
        WP_CLI::log('Pensez à purger le cache : wp-cli cloudflare cache_purge');
    }
}

WP_CLI::add_command('amnesty dedupe-events', 'amnesty_dedupe_events_command');
