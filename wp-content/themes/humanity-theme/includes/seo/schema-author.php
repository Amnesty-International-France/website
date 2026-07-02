<?php

declare(strict_types=1);

if (! function_exists('amnesty_seo_is_editorial_author_target')) {
    /**
     * Whether the current single view is an editorial content that must carry
     * the default author for Google Discover.
     *
     * Targets:
     *  - posts filed under the "Actualités", "Articles La Chronique" or
     *    "Dossiers" content-type categories;
     *  - "Repères" (landmark) pages.
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @return bool
     */
    function amnesty_seo_is_editorial_author_target(): bool
    {
        if (is_singular('landmark')) {
            return true;
        }

        if (is_singular('post')) {
            return has_category([ 'actualites', 'chronique', 'dossiers' ], get_queried_object_id());
        }

        return false;
    }
}

if (! function_exists('amnesty_seo_enable_landmark_author_support')) {
    /**
     * Enable author support on the "Repères" (landmark) post type.
     *
     * Yoast only builds an Article schema piece when the post type supports
     * authors (see Yoast\WP\SEO\Helpers\Schema\Article_Helper::is_author_supported).
     * The landmark CPT is registered without author support, so without this the
     * Article piece — and therefore the author — is never emitted on Repères.
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @return void
     */
    function amnesty_seo_enable_landmark_author_support(): void
    {
        add_post_type_support('landmark', 'author');
    }
}

// Runs after the landmark CPT is registered (init, default priority).
add_action('init', 'amnesty_seo_enable_landmark_author_support', 20);

if (! function_exists('amnesty_wpseo_force_landmark_article_type')) {
    /**
     * Make "Repères" (landmark) pages emit an Article schema piece.
     *
     * Yoast is configured with a schema article type of "None" for the landmark
     * post type, so no Article node is generated and there is nowhere to attach
     * an author. Force an Article type on those singles so the Article piece is
     * built (and can then receive the default author below).
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @param string|string[] $type the resolved article type
     *
     * @return string|string[]
     */
    function amnesty_wpseo_force_landmark_article_type($type)
    {
        if (is_singular('landmark') && 'None' === $type) {
            return 'Article';
        }

        return $type;
    }
}

add_filter('wpseo_schema_article_type', 'amnesty_wpseo_force_landmark_article_type');

if (! function_exists('amnesty_wpseo_default_author_schema')) {
    /**
     * Force the default author on the Yoast article schema.
     *
     * The branding plugin strips the author from the schema on singles
     * (see wp-plugin-amnesty-branding-main/includes/wpseo/author.php). For Google
     * Discover eligibility, editorial articles must carry an author, so we always
     * re-add "Amnesty International France" as an Organization on the targeted
     * content types. Hooked at a later priority so it runs after the branding
     * removal.
     *
     * @package Amnesty\Plugins\Yoast
     *
     * @param array $piece the article schema piece
     *
     * @return array
     */
    function amnesty_wpseo_default_author_schema(array $piece): array
    {
        if (! amnesty_seo_is_editorial_author_target()) {
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
