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

if (! function_exists('amnesty_filter_opengraph_url')) {
    /**
     * Force the Yoast og:url onto the production host.
     *
     * Yoast serves og:url from the precomputed indexable, so it bypasses the
     * `wpseo_canonical` filter and can still expose the preview (Infomaniak)
     * host. Normalise it explicitly.
     *
     * @param string $url The Open Graph URL.
     *
     * @return string The Open Graph URL on the production host.
     */
    function amnesty_filter_opengraph_url($url): string
    {
        return amnesty_normalise_canonical_host((string) $url);
    }
}

add_filter('wpseo_opengraph_url', 'amnesty_filter_opengraph_url');

if (! function_exists('amnesty_filter_schema_graph')) {
    /**
     * Force any preview host in the Yoast JSON-LD schema graph onto production.
     *
     * The schema `@id` / `url` nodes derive from the precomputed canonical, so
     * they can still carry the Infomaniak host. Walk the graph and normalise the
     * host of every absolute URL value.
     *
     * @param array $graph The Yoast schema graph (array of nodes).
     *
     * @return array The schema graph with production hosts only.
     */
    function amnesty_filter_schema_graph($graph): array
    {
        if (! is_array($graph)) {
            return $graph;
        }

        array_walk_recursive($graph, function (&$value) {
            if (is_string($value) && strpos($value, 'http') === 0) {
                $value = amnesty_normalise_canonical_host($value);
            }
        });

        return $graph;
    }
}

add_filter('wpseo_schema_graph', 'amnesty_filter_schema_graph');

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
