<?php

function tax_location_rewrite_rules()
{
    add_rewrite_rule(
        '^categorie/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?location=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^categorie/([^/]+)/?$',
        'index.php?location=$matches[1]',
        'top'
    );
}
add_action('init', 'tax_location_rewrite_rules');

/**
 * Point location term links at the URL the rules above actually serve.
 *
 * The taxonomy is registered with rewrite => ['slug' => 'pays'], the same base as
 * the fiche_pays post type. The post type wins that route (its permastruct is
 * added first, see the include order in functions.php), so get_term_link()
 * returns /pays/{slug}/, which renders the country sheet rather than the term
 * archive. The archive only exists at /categorie/{slug}/, which is why the
 * templates were building that link by hand.
 *
 * Doing it here instead of at each call site also gives Yoast the right value for
 * the XML sitemap and for the canonical of /categorie/{slug}/.
 */
add_filter('term_link', function (string $link, WP_Term $term, string $taxonomy): string {
    if ('location' !== $taxonomy) {
        return $link;
    }

    return home_url("/categorie/{$term->slug}/");
}, 10, 3);

/**
 * Bump when the location term link changes, so the reset below runs again.
 */
if (! defined('AMNESTY_LOCATION_INDEXABLES_VERSION')) {
    define('AMNESTY_LOCATION_INDEXABLES_VERSION', '1');
}

if (! function_exists('amnesty_reset_location_indexables')) {
    /**
     * Make Yoast rebuild the stored permalink of every location term, once.
     *
     * Yoast copies get_term_link() into wp_yoast_indexable when it builds a term
     * and serves the canonical, og:url, rel=next/prev and schema @id from that
     * copy. It only refreshes it when a taxonomy rewrite base changes, which the
     * term_link filter above does not do: the archives kept /pays/{slug}/ as
     * their canonical. Resetting the permalinks sets the indexables' version to
     * 0, so Yoast rebuilds each one, with the filtered link, on its next view.
     *
     * The applied version is stored in the `amnesty_location_indexables_version`
     * option.
     *
     * @return bool whether the reset ran
     */
    function amnesty_reset_location_indexables(): bool
    {
        if (!function_exists('YoastSEO')) {
            return false;
        }

        if (get_option('amnesty_location_indexables_version') === AMNESTY_LOCATION_INDEXABLES_VERSION) {
            return false;
        }

        YoastSEO()->helpers->indexable->reset_permalink_indexables('term', 'location');
        update_option('amnesty_location_indexables_version', AMNESTY_LOCATION_INDEXABLES_VERSION);

        return true;
    }
}

add_action('init', 'amnesty_reset_location_indexables', 20);
