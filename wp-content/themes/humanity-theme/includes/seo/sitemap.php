<?php

declare(strict_types=1);

/**
 * 1. Ensure the Sitemap directive is present in robots.txt.
 *    Yoast handles this when enable_xml_sitemap is on, but we add a
 *    high-priority fallback in case the option is off or Yoast is inactive.
 */
add_filter('robots_txt', function (string $output): string {
    if (strpos($output, 'Sitemap:') === false) {
        $output .= PHP_EOL . 'Sitemap: ' . esc_url(home_url('/sitemap_index.xml')) . PHP_EOL;
    }
    return $output;
}, 100000);

/**
 * 2. Flush rewrite rules once when Yoast sitemap routes are missing.
 *    This fixes a 404 on /sitemap_index.xml after plugin activation or theme
 *    changes without a manual Settings > Permalinks save.
 */
add_action('init', function (): void {
    if (!defined('WPSEO_VERSION')) {
        return;
    }

    if (get_transient('amnesty_sitemap_rules_ok')) {
        return;
    }

    $rules = get_option('rewrite_rules');
    if (!is_array($rules) || !preg_grep('/sitemap_index\.xml/', array_keys($rules))) {
        flush_rewrite_rules(false);
    }

    set_transient('amnesty_sitemap_rules_ok', true, WEEK_IN_SECONDS);
}, 999);

/**
 * 3. Exclude post types that are not publicly queryable from the Yoast sitemap.
 *    Yoast checks public => true, but some types have public => true yet
 *    publicly_queryable => false (e.g. the 'sidebar' CPT), meaning their
 *    frontend URLs do not exist and should never appear in a sitemap.
 *
 *    Also excludes post types whose content requires authentication to access
 *    (e.g. 'actualities-my-space', scoped to the logged-in user's personal space).
 */
add_filter('wpseo_sitemap_exclude_post_type', function (bool $excluded, string $post_type): bool {
    if ($excluded) {
        return true;
    }

    $pto = get_post_type_object($post_type);

    if (!$pto instanceof WP_Post_Type) {
        return true;
    }

    if (!$pto->publicly_queryable) {
        return true;
    }

    $auth_required_types = ['actualities-my-space'];

    return in_array($post_type, $auth_required_types, true);
}, 10, 2);

/**
 * 3c. Exclude taxonomies with no public rewrite rules (rewrite => false).
 *     Their term links fall back to the ugly /?taxonomy=…&term=… format,
 *     which has no SEO value and should not be crawled.
 */
add_filter('wpseo_sitemap_exclude_taxonomy', function (bool $excluded, string $taxonomy): bool {
    if ($excluded) {
        return true;
    }

    $tax = get_taxonomy($taxonomy);

    if (!$tax instanceof WP_Taxonomy) {
        return true;
    }

    return false === $tax->rewrite;
}, 10, 2);

/**
 * 4. Safety net on individual sitemap entries:
 *    - Drop posts that are not published or are password-protected.
 *    - Drop training posts where the ACF field 'members_only' is true.
 *    - Drop any URL whose path starts with /mon-espace/ (auth-gated area).
 */
add_filter('wpseo_sitemap_entry', function (mixed $url, string $_post_type, object $post): mixed {
    if (empty($url) || !isset($post->ID)) {
        return $url;
    }

    if ('publish' !== get_post_status($post->ID)) {
        return false;
    }

    if (!empty(get_post_field('post_password', $post->ID))) {
        return false;
    }

    if ('training' === get_post_type($post->ID) && function_exists('get_field') && get_field('members_only', $post->ID)) {
        return false;
    }

    if (function_exists('amnesty_document_is_private') && amnesty_document_is_private($post->ID)) {
        return false;
    }

    $path = wp_parse_url($url['loc'] ?? '', PHP_URL_PATH);
    if ($path && str_starts_with($path, '/mon-espace/')) {
        return false;
    }

    return $url;
}, 10, 3);
