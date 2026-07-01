<?php

declare(strict_types=1);

if (! function_exists('amnesty_wpseo_default_author_schema')) {
    /**
     * Force the default author on the Yoast article schema.
     *
     * The branding plugin strips the author from the schema on singles
     * (see wp-plugin-amnesty-branding-main/includes/wpseo/author.php). For Google
     * Discover eligibility every editorial article must carry an author, so we
     * always re-add "Amnesty International France" as an Organization. Hooked at a
     * later priority so it runs after the branding removal.
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @param array $piece the article schema piece
     *
     * @return array
     */
    function amnesty_wpseo_default_author_schema(array $piece): array
    {
        if (! is_single()) {
            return $piece;
        }

        $piece['author'] = [
            '@type' => 'Organization',
            'name'  => 'Amnesty International France',
            'url'   => home_url('/'),
        ];

        return $piece;
    }
}

add_filter('wpseo_schema_article', 'amnesty_wpseo_default_author_schema', 20);
