<?php

declare(strict_types=1);

if (! function_exists('amnesty_yoast_fix_post_breadcrumb_schema')) {
    /**
     * Fix schema breadcrumbs from Yoast on post singles
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @param array|null $piece the schema array
     *
     * @return array|null
     */
    function amnesty_yoast_fix_post_breadcrumb_schema(?array $piece): ?array
    {
        if (! is_single() || ! is_array($piece)) {
            return $piece;
        }

        // handle documents separately
        if ('attachment' === get_post_type() && 'application/pdf' === get_post_mime_type()) {
            return $piece;
        }

        $home = trailingslashit(home_url('/', 'https'));
        $link = get_permalink();

        // can't do anything about it
        if (! $link) {
            return $piece;
        }

        $bits                       = explode('/', trim(str_replace($home, '', $link), '/'));
        $base                       = $home;
        $items                      = [];
        $chronicle_category         = false;
        $chronicle_articles_url     = '';
        $articles_archive_url       = '';
        $has_chronicle_schema_patch = function_exists('amnesty_is_chronicle_article')
            && amnesty_is_chronicle_article()
            && function_exists('amnesty_get_chronicle_articles_category')
            && function_exists('amnesty_get_chronicle_articles_url')
            && function_exists('amnesty_get_articles_archive_url')
            && function_exists('amnesty_url_paths_match');

        if ($has_chronicle_schema_patch) {
            $chronicle_category     = amnesty_get_chronicle_articles_category();
            $chronicle_articles_url = amnesty_get_chronicle_articles_url();
            $articles_archive_url   = amnesty_get_articles_archive_url();
        }

        foreach ($bits as $bit) {
            $base .= trailingslashit($bit);
            $item_url = $base;
            $item_name = ucwords(str_replace('-', ' ', $bit));

            if (
                $chronicle_category
                && $articles_archive_url
                && amnesty_url_paths_match($item_url, $articles_archive_url)
            ) {
                $item_url  = $chronicle_articles_url;
                $item_name = $chronicle_category->name;
            }

            $items[] = [
                '@type'    => 'ListItem',
                'position' => count($items) + 1,
                'item'     => [
                    '@type' => 'WebPage',
                    '@id'   => $item_url,
                    'url'   => $item_url,
                    'name'  => $item_name,
                ],
            ];
        }

        $piece['itemListElement'] = $items;

        return $piece;
    }
}

// Fix breadcrumbs for single posts
add_filter('wpseo_schema_breadcrumb', 'amnesty_yoast_fix_post_breadcrumb_schema');
