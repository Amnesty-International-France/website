<?php

declare(strict_types=1);

if (! function_exists('amnesty_normalise_canonical_host')) {
    /**
     * Force a canonical URL onto the production host.
     *
     * Some canonicals were stored while editing on the Infomaniak preview
     * environment, leaving the wrong (and inaccessible) host in `rel=canonical`.
     * This rewrites the scheme + host to the official site URL while keeping the
     * path and query intact, so pages stay self-canonical on www.amnesty.fr.
     *
     * @param string|null $canonical The canonical URL to normalise.
     *
     * @return string The canonical URL on the production host, or '' if empty.
     */
    function amnesty_normalise_canonical_host(?string $canonical): string
    {
        if (empty($canonical)) {
            return '';
        }

        $home_host      = wp_parse_url(home_url(), PHP_URL_HOST);
        $canonical_host = wp_parse_url($canonical, PHP_URL_HOST);

        if (! $home_host || ! $canonical_host || $canonical_host === $home_host) {
            return $canonical;
        }

        $path  = (string) (wp_parse_url($canonical, PHP_URL_PATH) ?: '/');
        $query = wp_parse_url($canonical, PHP_URL_QUERY);

        return home_url($path . ($query ? '?' . $query : ''));
    }
}

if (! function_exists('amnesty_render_canonical')) {
    /**
     * Render the canonical href on posts
     *
     * @return void
     */
    function amnesty_render_canonical(): void
    {
        if (is_admin() || ! is_single()) {
            return;
        }

        $canonical = get_post_meta(get_the_ID(), '_yoast_wpseo_canonical', true);

        if (! $canonical) {
            return;
        }

        $canonical = amnesty_normalise_canonical_host((string) $canonical);

        printf('<link rel="canonical" href="%s">', esc_url($canonical));
    }
}

add_action('wp_head', 'amnesty_render_canonical');

if (! function_exists('amnesty_wpseo_canonical_filter')) {
    /**
     * Remove erroneous canonicals from search results/filters
     *
     * @package Amnesty
     *
     * @param string|null $canonical the canonical URI
     *
     * @return string
     */
    function amnesty_wpseo_canonical_filter(?string $canonical = ''): string
    {
        if (get_queried_object_id() !== absint(get_option('amnesty_search_page'))) {
            return amnesty_normalise_canonical_host($canonical);
        }

        if (is_paged()) {
            return '';
        }

        $query_string = query_string_to_array(wp_parse_url(current_url(), PHP_URL_QUERY) ?: '');

        if (! empty($query_string)) {
            return '';
        }

        return amnesty_normalise_canonical_host($canonical);
    }
}

add_filter('wpseo_canonical', 'amnesty_wpseo_canonical_filter');
