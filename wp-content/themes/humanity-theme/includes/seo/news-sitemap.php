<?php

declare(strict_types=1);

/**
 * Google News sitemap.
 *
 * Registers a `news-sitemap.xml` endpoint within the Yoast SEO sitemap
 * framework, following Google's News sitemap specification:
 *
 * @link https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap
 *
 * Only articles published within the last two days are listed (Google ignores
 * older entries for News), capped at 1000 URLs per the specification. The
 * sitemap is also advertised inside Yoast's `sitemap_index.xml`.
 *
 * Yoast's router already maps `news-sitemap.xml` to `index.php?sitemap=news`
 * via its generic `([^/]+?)-sitemap\.xml$` rewrite rule, so no extra rule is
 * required here.
 */

// The sitemap identifier, used to build the `news-sitemap.xml` URL.
if (!defined('AMNESTY_NEWS_SITEMAP_NAME')) {
    define('AMNESTY_NEWS_SITEMAP_NAME', 'news');
}

// Maximum age of an article to be listed (Google: 2 days).
if (!defined('AMNESTY_NEWS_SITEMAP_MAX_AGE')) {
    define('AMNESTY_NEWS_SITEMAP_MAX_AGE', 2 * DAY_IN_SECONDS);
}

// Maximum number of URLs in a News sitemap (Google specification).
if (!defined('AMNESTY_NEWS_SITEMAP_MAX_ENTRIES')) {
    define('AMNESTY_NEWS_SITEMAP_MAX_ENTRIES', 1000);
}

if (!function_exists('amnesty_news_sitemap_post_types')) {
    /**
     * Retrieve the post types eligible for the News sitemap.
     *
     * @return array<int,string>
     */
    function amnesty_news_sitemap_post_types(): array
    {
        /**
         * Filter the post types included in the Google News sitemap.
         *
         * @param array<int,string> $post_types Defaults to standard posts (Actualités).
         */
        $post_types = (array) apply_filters('amnesty_news_sitemap_post_types', ['post']);

        return array_values(array_filter(array_map('strval', $post_types)));
    }
}

if (!function_exists('amnesty_news_sitemap_publication_name')) {
    /**
     * Retrieve the publication name as it should appear in Google News.
     *
     * @return string
     */
    function amnesty_news_sitemap_publication_name(): string
    {
        /**
         * Filter the publication name used in the `<news:name>` tag.
         *
         * @param string $name The publication name.
         */
        return (string) apply_filters('amnesty_news_sitemap_publication_name', 'Amnesty International France');
    }
}

if (!function_exists('amnesty_news_sitemap_language')) {
    /**
     * Retrieve the ISO 639 language code for the `<news:language>` tag.
     *
     * @return string
     */
    function amnesty_news_sitemap_language(): string
    {
        // Google expects a short ISO 639 code (e.g. "fr"), not the WP locale "fr_FR".
        $locale = strtolower((string) get_bloginfo('language'));
        $parts  = preg_split('/[-_]/', $locale);

        $language = (is_array($parts) && '' !== $parts[0]) ? $parts[0] : 'fr';

        /**
         * Filter the language code used in the `<news:language>` tag.
         *
         * @param string $language The ISO 639 language code.
         */
        return (string) apply_filters('amnesty_news_sitemap_language', $language);
    }
}

if (!function_exists('amnesty_news_sitemap_is_noindexed')) {
    /**
     * Whether a post has been flagged as noindex in Yoast SEO.
     *
     * @param WP_Post $post the post to check
     *
     * @return bool
     */
    function amnesty_news_sitemap_is_noindexed(WP_Post $post): bool
    {
        if (!class_exists('WPSEO_Meta')) {
            return false;
        }

        return '1' === WPSEO_Meta::get_value('meta-robots-noindex', $post->ID);
    }
}

if (!function_exists('amnesty_news_sitemap_posts')) {
    /**
     * Query the articles eligible for the News sitemap.
     *
     * Published articles from the last two days, most recent first, capped at
     * the Google maximum, excluding any flagged as noindex in Yoast.
     *
     * @return array<int,WP_Post>
     */
    function amnesty_news_sitemap_posts(): array
    {
        $query = new WP_Query([
            'post_type'              => amnesty_news_sitemap_post_types(),
            'post_status'            => 'publish',
            'posts_per_page'         => AMNESTY_NEWS_SITEMAP_MAX_ENTRIES,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'date_query'             => [
                [
                    'after'     => gmdate('Y-m-d H:i:s', time() - AMNESTY_NEWS_SITEMAP_MAX_AGE),
                    'column'    => 'post_date_gmt',
                    'inclusive' => true,
                ],
            ],
        ]);

        $posts = array_filter(
            $query->posts,
            static fn (WP_Post $post): bool => !amnesty_news_sitemap_is_noindexed($post)
        );

        return array_values($posts);
    }
}

if (!function_exists('amnesty_news_sitemap_escape_text')) {
    /**
     * Escape a string for safe inclusion as XML text content.
     *
     * @param string $text the raw text
     *
     * @return string
     */
    function amnesty_news_sitemap_escape_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

if (!function_exists('amnesty_news_sitemap_escape_url')) {
    /**
     * Escape a URL for safe inclusion in an XML sitemap.
     *
     * Mirrors Yoast's own handling: prefer the spec-friendly `&amp;`/`&apos;`
     * entities over the numeric ones esc_url() produces.
     *
     * @param string $url the raw URL
     *
     * @return string
     */
    function amnesty_news_sitemap_escape_url(string $url): string
    {
        $url = esc_url($url);

        return str_replace(['&#038;', '&#039;'], ['&amp;', '&apos;'], $url);
    }
}

if (!function_exists('amnesty_news_sitemap_render')) {
    /**
     * Build and register the News sitemap output with Yoast.
     *
     * Hooked to `wpseo_do_sitemap_news`; Yoast handles headers, the XML
     * declaration and final output once the content is set.
     *
     * @return void
     */
    function amnesty_news_sitemap_render(): void
    {
        if (empty($GLOBALS['wpseo_sitemaps'])) {
            return;
        }

        $publication = amnesty_news_sitemap_escape_text(amnesty_news_sitemap_publication_name());
        $language    = amnesty_news_sitemap_escape_text(amnesty_news_sitemap_language());

        $urlset  = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $urlset .= 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

        foreach (amnesty_news_sitemap_posts() as $post) {
            $published = get_post_datetime($post);

            $urlset .= "\t<url>\n";
            $urlset .= "\t\t<loc>" . amnesty_news_sitemap_escape_url((string) get_permalink($post)) . "</loc>\n";
            $urlset .= "\t\t<news:news>\n";
            $urlset .= "\t\t\t<news:publication>\n";
            $urlset .= "\t\t\t\t<news:name>{$publication}</news:name>\n";
            $urlset .= "\t\t\t\t<news:language>{$language}</news:language>\n";
            $urlset .= "\t\t\t</news:publication>\n";

            if ($published instanceof DateTimeImmutable) {
                $urlset .= "\t\t\t<news:publication_date>" . esc_html($published->format('c')) . "</news:publication_date>\n";
            }

            $urlset .= "\t\t\t<news:title>" . amnesty_news_sitemap_escape_text(get_the_title($post)) . "</news:title>\n";
            $urlset .= "\t\t</news:news>\n";
            $urlset .= "\t</url>\n";
        }

        $urlset .= '</urlset>';

        // Yoast's default stylesheet only knows the standard columns; drop it
        // rather than render a broken preview for the News-specific tags.
        $GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet('');
        $GLOBALS['wpseo_sitemaps']->set_sitemap($urlset);
    }
}

/**
 * Register the News sitemap with Yoast SEO.
 *
 * Yoast instantiates its sitemaps object on `init`; register just after.
 */
add_action('init', function (): void {
    if (empty($GLOBALS['wpseo_sitemaps'])) {
        return;
    }

    $GLOBALS['wpseo_sitemaps']->register_sitemap(AMNESTY_NEWS_SITEMAP_NAME, 'amnesty_news_sitemap_render');
}, 11);

/**
 * Advertise the News sitemap inside Yoast's `sitemap_index.xml`.
 *
 * Only listed when at least one article falls within the news window, to
 * avoid pointing search engines at an empty sitemap.
 */
add_filter('wpseo_sitemap_index', function (string $appended): string {
    $posts = amnesty_news_sitemap_posts();

    if (!$posts) {
        return $appended;
    }

    $loc      = amnesty_news_sitemap_escape_url(home_url('/' . AMNESTY_NEWS_SITEMAP_NAME . '-sitemap.xml'));
    $modified = get_post_datetime($posts[0], 'modified');

    $appended .= "\t<sitemap>\n";
    $appended .= "\t\t<loc>{$loc}</loc>\n";

    if ($modified instanceof DateTimeImmutable) {
        $appended .= "\t\t<lastmod>" . esc_html($modified->format('c')) . "</lastmod>\n";
    }

    $appended .= "\t</sitemap>\n";

    return $appended;
});
