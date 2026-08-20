<?php

declare(strict_types=1);

/**
 * One-shot cleanup for canonical URLs stored with the wrong host or a dirty path.
 *
 * The `wpseo_canonical` / `wpseo_opengraph_url` / `wpseo_schema_graph` filters in
 * includes/seo/canonical.php fix the rendered HTML, but the bad values remain in
 * the database and leak through anything that reads them directly (Yoast
 * sitemaps, REST, exports). This command rewrites them in place.
 *
 * Usage:
 *   wp amnesty fix-canonical-urls --dry-run
 *   wp amnesty fix-canonical-urls
 */

if (! function_exists('amnesty_canonical_host_is_faulty')) {
    /**
     * Whether a host is a known preview/staging host that leaked into stored
     * canonicals and must be rewritten onto production.
     *
     * @param string $host the URL host
     *
     * @return bool
     */
    function amnesty_canonical_host_is_faulty(string $host): bool
    {
        // Editing on the Infomaniak preview environment stored its host in
        // `rel=canonical`. Extend this list if other preview hosts surface.
        $needles = [ 'infomaniak' ];

        foreach ($needles as $needle) {
            if (false !== stripos($host, $needle)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('amnesty_canonical_needs_cleanup')) {
    /**
     * Whether a stored URL should be rewritten in the database.
     *
     * Unlike the runtime `wpseo_canonical` filter, this migration is
     * destructive, so it stays conservative: only known faulty hosts and dirty
     * paths on the production host are touched. An intentional external
     * canonical (e.g. www.amnesty.org) keeps its host untouched.
     *
     * @param string $url the stored URL
     *
     * @return bool
     */
    function amnesty_canonical_needs_cleanup(string $url): bool
    {
        if ('' === trim($url)) {
            return false;
        }

        $host = wp_parse_url($url, PHP_URL_HOST);

        // Relative URLs carry no host and are left untouched by the normaliser.
        if (! $host) {
            return false;
        }

        // A known preview/staging host is always forced back to production.
        if (amnesty_canonical_host_is_faulty((string) $host)) {
            return true;
        }

        $home_host = wp_parse_url(home_url(), PHP_URL_HOST);

        // Any other foreign host may be a deliberate external canonical -
        // never overwrite it during a DB migration.
        if ($home_host && $host !== $home_host) {
            return false;
        }

        // Same host: clean up only when the path/query normalisation changes it.
        return amnesty_normalise_canonical_host($url) !== $url;
    }
}

if (! function_exists('amnesty_fix_canonical_postmeta')) {
    /**
     * Clean the `_yoast_wpseo_canonical` post meta.
     *
     * A meta whose normalised value equals the post permalink is removed
     * altogether: Yoast then falls back to the implicit self-canonical.
     *
     * @global wpdb $wpdb
     *
     * @param bool $dry_run whether to report without writing
     *
     * @return array{inspected:int,updated:int,deleted:int}
     */
    function amnesty_fix_canonical_postmeta(bool $dry_run): array
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
                '_yoast_wpseo_canonical'
            )
        );

        $stats = [ 'inspected' => count($rows), 'updated' => 0, 'deleted' => 0 ];

        foreach ($rows as $row) {
            $stored = (string) $row->meta_value;

            if (! amnesty_canonical_needs_cleanup($stored)) {
                continue;
            }

            $post_id   = (int) $row->post_id;
            $clean     = amnesty_normalise_canonical_host($stored);
            $permalink = get_permalink($post_id);

            if ($permalink && amnesty_normalise_canonical_host($permalink) === $clean) {
                WP_CLI::log(sprintf('post %d: drop meta (self-canonical) - was %s', $post_id, $stored));

                if (! $dry_run) {
                    delete_post_meta($post_id, '_yoast_wpseo_canonical');
                }

                $stats['deleted']++;

                continue;
            }

            WP_CLI::log(sprintf('post %d: %s -> %s', $post_id, $stored, $clean));

            if (! $dry_run) {
                update_post_meta($post_id, '_yoast_wpseo_canonical', $clean);
            }

            $stats['updated']++;
        }

        return $stats;
    }
}

if (! function_exists('amnesty_fix_canonical_indexables')) {
    /**
     * Clean the `canonical` and `permalink` columns of the Yoast indexable table.
     *
     * @global wpdb $wpdb
     *
     * @param bool $dry_run whether to report without writing
     *
     * @return array{inspected:int,updated:int}
     */
    function amnesty_fix_canonical_indexables(bool $dry_run): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'yoast_indexable';

        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            WP_CLI::warning(sprintf('Table %s introuvable, étape ignorée.', $table));

            return [ 'inspected' => 0, 'updated' => 0 ];
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results("SELECT id, permalink, canonical FROM {$table}");

        $stats = [ 'inspected' => count($rows), 'updated' => 0 ];

        foreach ($rows as $row) {
            $update = [];

            foreach ([ 'permalink', 'canonical' ] as $column) {
                $stored = (string) ($row->{$column} ?? '');

                if (! amnesty_canonical_needs_cleanup($stored)) {
                    continue;
                }

                $update[$column] = amnesty_normalise_canonical_host($stored);

                WP_CLI::log(sprintf('indexable %d [%s]: %s -> %s', $row->id, $column, $stored, $update[$column]));
            }

            if (! $update) {
                continue;
            }

            // Yoast indexes lookups by permalink_hash (strlen:md5 of the
            // permalink). Recompute it or those lookups miss the fixed rows.
            if (isset($update['permalink'])) {
                $update['permalink_hash'] = strlen($update['permalink']) . ':' . md5($update['permalink']);
            }

            if (! $dry_run) {
                $wpdb->update($table, $update, [ 'id' => (int) $row->id ]);
            }

            $stats['updated']++;
        }

        return $stats;
    }
}

if (! function_exists('amnesty_fix_canonical_urls_command')) {
    /**
     * WP-CLI entrypoint.
     *
     * @param array $args       positional arguments (unused)
     * @param array $assoc_args associative arguments
     *
     * @return void
     */
    function amnesty_fix_canonical_urls_command($args, $assoc_args): void
    {
        $dry_run = (bool) ($assoc_args['dry-run'] ?? false);

        if ($dry_run) {
            WP_CLI::log('Mode simulation : aucune écriture en base.');
        }

        $meta       = amnesty_fix_canonical_postmeta($dry_run);
        $indexables = amnesty_fix_canonical_indexables($dry_run);

        WP_CLI::success(sprintf(
            '_yoast_wpseo_canonical : %d inspectés, %d corrigés, %d supprimés.',
            $meta['inspected'],
            $meta['updated'],
            $meta['deleted']
        ));

        WP_CLI::success(sprintf(
            'yoast_indexable : %d inspectés, %d corrigés.',
            $indexables['inspected'],
            $indexables['updated']
        ));

        if ($dry_run) {
            WP_CLI::log('Relancer sans --dry-run pour appliquer.');

            return;
        }

        WP_CLI::log('Pensez à purger le cache : wp-cli cloudflare cache_purge');
    }
}

WP_CLI::add_command('amnesty fix-canonical-urls', 'amnesty_fix_canonical_urls_command');
