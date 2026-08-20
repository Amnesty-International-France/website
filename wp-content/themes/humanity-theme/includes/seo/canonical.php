<?php

declare(strict_types=1);

if (! function_exists('amnesty_normalise_url_path')) {
    /**
     * Resolve dot segments and duplicate slashes out of a URL path.
     *
     * Some stored canonicals carry a `/./` segment, which Google treats as a
     * distinct URL from the clean one. Rebuild the path from its meaningful
     * segments, keeping the trailing slash when there was one.
     *
     * @param string $path The URL path to normalise.
     *
     * @return string The normalised, absolute path.
     */
    function amnesty_normalise_url_path(string $path): string
    {
        if ('' === $path) {
            return '/';
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ('' === $segment || '.' === $segment) {
                continue;
            }

            if ('..' === $segment) {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        $normalised = '/' . implode('/', $segments);

        if ('/' !== $normalised && str_ends_with($path, '/')) {
            $normalised .= '/';
        }

        return $normalised;
    }
}

if (! function_exists('amnesty_normalise_canonical_host')) {
    /**
     * Force a canonical URL onto the production host.
     *
     * Some canonicals were stored while editing on the Infomaniak preview
     * environment, leaving the wrong (and inaccessible) host in `rel=canonical`.
     * This rewrites the scheme + host to the official site URL while keeping the
     * path and query intact, so pages stay self-canonical on www.amnesty.fr.
     *
     * The path is normalised in every case, including when the host is already
     * the right one: a canonical such as `https://www.amnesty.fr/./actualites/`
     * has a valid host but still points at a URL Google sees as a duplicate.
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

        $canonical_host = wp_parse_url($canonical, PHP_URL_HOST);

        // Relative URLs carry no host to rewrite - leave them untouched.
        if (! $canonical_host) {
            return $canonical;
        }

        $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $path      = amnesty_normalise_url_path((string) (wp_parse_url($canonical, PHP_URL_PATH) ?: '/'));
        $query     = wp_parse_url($canonical, PHP_URL_QUERY);
        $fragment  = wp_parse_url($canonical, PHP_URL_FRAGMENT);

        // Yoast schema @id nodes are URLs with a fragment (#webpage, #organization).
        $suffix = $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');

        if ($home_host && $canonical_host !== $home_host) {
            return home_url($suffix);
        }

        $scheme = wp_parse_url($canonical, PHP_URL_SCHEME) ?: 'https';
        $port   = wp_parse_url($canonical, PHP_URL_PORT);

        return sprintf('%s://%s%s%s', $scheme, $canonical_host, $port ? ':' . $port : '', $suffix);
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

if (! function_exists('amnesty_wpseo_canonical_filter')) {
    /**
     * Remove erroneous canonicals from search results/filters
     *
     * Only the search page itself may end up without a canonical: everything
     * else - including post type archives, whose queried object ID is 0 like an
     * unset `amnesty_search_page` option - stays self-canonical.
     *
     * @package Amnesty
     *
     * @param string|null $canonical the canonical URI
     *
     * @return string
     */
    function amnesty_wpseo_canonical_filter(?string $canonical = ''): string
    {
        $search_page = absint(get_option('amnesty_search_page'));

        if (0 === $search_page || get_queried_object_id() !== $search_page) {
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
