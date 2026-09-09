<?php

declare(strict_types=1);

if (! function_exists('amnesty_get_articles_archive_url')) {
    /**
     * Build the articles archive URL with optional query arguments.
     *
     * @param array<string,int|string> $query_args Query arguments to append.
     *
     * @return string
     */
    function amnesty_get_articles_archive_url(array $query_args = []): string
    {
        $articles_page = get_page_by_path('sinformer/articles');
        $articles_url  = $articles_page ? get_permalink($articles_page) : false;

        if (! $articles_url) {
            $articles_url = home_url('/sinformer/articles/');
        }

        if (empty($query_args)) {
            return $articles_url;
        }

        return add_query_arg($query_args, $articles_url);
    }
}

if (! function_exists('amnesty_get_chronicle_articles_category')) {
    /**
     * Retrieve the category used for "La Chronique" articles.
     *
     * @return WP_Term|false
     */
    function amnesty_get_chronicle_articles_category(): WP_Term|false
    {
        $chronicle_category_slugs = [ 'chronique', 'chroniques' ];
        $post_categories          = get_the_category();

        foreach ($post_categories as $post_category) {
            if (in_array($post_category->slug, $chronicle_category_slugs, true)) {
                return $post_category;
            }
        }

        foreach ($chronicle_category_slugs as $slug) {
            $chronicle_category = get_category_by_slug($slug);

            if ($chronicle_category && ! is_wp_error($chronicle_category)) {
                return $chronicle_category;
            }
        }

        return false;
    }
}

if (! function_exists('amnesty_is_chronicle_article')) {
    /**
     * Check whether the current singular post is a "La Chronique" article.
     *
     * @return bool
     */
    function amnesty_is_chronicle_article(): bool
    {
        return is_singular('post') && has_category([ 'chronique', 'chroniques' ], get_queried_object_id());
    }
}

if (! function_exists('amnesty_url_paths_match')) {
    /**
     * Compare two URLs by path only.
     *
     * @param string $first_url  First URL.
     * @param string $second_url Second URL.
     *
     * @return bool
     */
    function amnesty_url_paths_match(string $first_url, string $second_url): bool
    {
        $first_path  = wp_parse_url($first_url, PHP_URL_PATH);
        $second_path = wp_parse_url($second_url, PHP_URL_PATH);

        return $first_path && $second_path && untrailingslashit($first_path) === untrailingslashit($second_path);
    }
}

if (! function_exists('amnesty_get_chronicle_articles_url')) {
    /**
     * Build the filtered articles archive URL for "La Chronique" articles.
     *
     * @return string
     */
    function amnesty_get_chronicle_articles_url(): string
    {
        $chronicle_category = amnesty_get_chronicle_articles_category();

        if (! $chronicle_category) {
            return amnesty_get_articles_archive_url();
        }

        return amnesty_get_articles_archive_url(
            [
                'qcategory' => (int) $chronicle_category->term_id,
            ]
        );
    }
}

if (! function_exists('amnesty_is_chronicle_article_breadcrumb_link')) {
    /**
     * Check whether a Yoast breadcrumb link represents the "La Chronique" article category.
     *
     * @param array<string,mixed> $link               Yoast breadcrumb link.
     * @param WP_Term             $chronicle_category The "La Chronique" category.
     *
     * @return bool
     */
    function amnesty_is_chronicle_article_breadcrumb_link(array $link, WP_Term $chronicle_category): bool
    {
        if (isset($link['term_id']) && (int) $link['term_id'] === (int) $chronicle_category->term_id) {
            return true;
        }

        if (($link['term'] ?? null) instanceof WP_Term && (int) $link['term']->term_id === (int) $chronicle_category->term_id) {
            return true;
        }

        if (empty($link['url'])) {
            return false;
        }

        $category_url = get_category_link($chronicle_category->term_id);

        if (is_wp_error($category_url)) {
            return false;
        }

        return amnesty_url_paths_match((string) $link['url'], $category_url);
    }
}

if (! function_exists('amnesty_custom_chronicle_article_breadcrumbs')) {
    /**
     * Point the "La Chronique" article category breadcrumb to the filtered articles archive.
     *
     * @param array<int,array<string,mixed>> $links Yoast breadcrumb links.
     *
     * @return array<int,array<string,mixed>>
     */
    function amnesty_custom_chronicle_article_breadcrumbs(array $links): array
    {
        if (! amnesty_is_chronicle_article()) {
            return $links;
        }

        $chronicle_category = amnesty_get_chronicle_articles_category();

        if (! $chronicle_category) {
            return $links;
        }

        $has_chronicle_breadcrumb = false;

        foreach ($links as $index => $link) {
            if (amnesty_is_chronicle_article_breadcrumb_link($link, $chronicle_category)) {
                $links[ $index ]['url']   = amnesty_get_chronicle_articles_url();
                $has_chronicle_breadcrumb = true;
            }
        }

        if (! $has_chronicle_breadcrumb) {
            array_splice(
                $links,
                max(1, count($links) - 1),
                0,
                [
                    [
                        'url'  => amnesty_get_chronicle_articles_url(),
                        'text' => $chronicle_category->name,
                    ],
                ]
            );
        }

        return $links;
    }
}

add_filter('wpseo_breadcrumb_links', 'amnesty_custom_chronicle_article_breadcrumbs', 20);
