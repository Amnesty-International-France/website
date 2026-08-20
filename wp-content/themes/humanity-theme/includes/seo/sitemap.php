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

    // Core registers 'page' with publicly_queryable => false (wp-includes/post.php):
    // pages resolve through `pagename`, not a query var. Without this exemption the
    // check below silently drops every page from the sitemap.
    if ('page' !== $post_type && !$pto->publicly_queryable) {
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
 * 3d. Drop the author sitemap.
 *     Author archives carry no editorial value here — the branding plugin already
 *     strips author metadata from the frontend — and their slugs derive from
 *     usernames that are email addresses (/author/prenom-nomdomaine-fr/), so
 *     listing them exposes contributor addresses. Yoast omits the sitemap entirely
 *     once the filter leaves no user. The archives themselves are left untouched.
 */
add_filter('wpseo_sitemap_exclude_author', '__return_empty_array');

/**
 * Slugs of the top-level pages rendered by an account template.
 *
 * Each one maps to a page-{slug}.php at the theme root. They are transactional
 * steps of the donor space (login, email verification, password reset, newsletter
 * preview…), driven by query-string arguments and rendering an error state
 * without them, so they have no standalone value in an index. The /mon-espace/
 * prefix check below does not reach them because they sit at the root.
 *
 * Nested pages never match a page-{slug}.php template: get_page_template() builds
 * the candidate from the full `pagename` path, so only top-level pages are listed
 * here. Excluding a slug that does not exist is a harmless no-op.
 */
if (!defined('AMNESTY_SITEMAP_ACCOUNT_PAGE_SLUGS')) {
    define('AMNESTY_SITEMAP_ACCOUNT_PAGE_SLUGS', [
        'connectez-vous',
        'creer-votre-compte',
        'mes-demandes',
        'mes-dons',
        'mes-informations-personnelles',
        'mes-recus-fiscaux',
        'modification-coordonnees-bancaire',
        'modifier-mon-mot-de-passe',
        'mot-de-passe-oublie',
        'se-deconnecter',
        'verifier-votre-email',
        'voir-newsletter',
    ]);
}

/**
 * 4. Safety net on individual sitemap entries:
 *    - Drop posts that are not published or are password-protected.
 *    - Drop training posts where the ACF field 'members_only' is true.
 *    - Drop any URL whose path starts with /mon-espace/ (auth-gated area).
 *    - Drop the top-level account pages, which live outside /mon-espace/.
 *    - Drop the contentless section pages that answer with a 301 (MAINT-290);
 *      a redirecting URL in a sitemap is reported as a soft error by Search Console.
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

    if ($path && function_exists('amnesty_get_section_page_redirects')) {
        $redirected_paths = array_map(
            fn (string $slug): string => '/' . $slug,
            array_keys(amnesty_get_section_page_redirects())
        );

        if (in_array(untrailingslashit($path), $redirected_paths, true)) {
            return false;
        }
    }

    $post_object = get_post($post->ID);
    if (
        $post_object instanceof WP_Post
        && 'page' === $post_object->post_type
        && 0 === (int) $post_object->post_parent
        && in_array($post_object->post_name, AMNESTY_SITEMAP_ACCOUNT_PAGE_SLUGS, true)
    ) {
        return false;
    }

    return $url;
}, 10, 3);

/**
 * 5. Redirect the retired Jetpack sitemap URLs to the Yoast index.
 *    Jetpack served these by matching REQUEST_URI, not through rewrite rules
 *    (see modules/sitemaps/sitemaps.php::callback_action_catch_sitemap_urls), so
 *    they all become plain 404s once the module is gone. They were advertised in
 *    robots.txt and listed in the old master sitemap, so send crawlers to the new
 *    index rather than leaving them on dead ends.
 *
 *    Yoast already redirects the bare /sitemap.xml on template_redirect at
 *    priority 0 (WPSEO_Sitemaps_Router::template_redirect), but only when the
 *    full request URI matches exactly, so it misses a query string. This runs at
 *    the default priority and covers the children plus that case.
 *
 *    Not matched on purpose: /news-sitemap.xml, which is a live sitemap of ours
 *    served through Yoast (see news-sitemap.php), and the .xsl stylesheets, which
 *    were only ever referenced from the retired XML itself.
 *
 *    The pattern deliberately does not match Yoast's own /sitemap_index.xml
 *    (underscore) nor its /{type}-sitemap.xml children.
 */
add_action('template_redirect', function (): void {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    $path        = wp_parse_url($request_uri, PHP_URL_PATH);

    if (!is_string($path) || !preg_match('#^/(?:image-|video-)?sitemap(?:-index)?(?:-\d+)?\.xml$#', $path)) {
        return;
    }

    wp_safe_redirect(home_url('/sitemap_index.xml'), 301);
    exit;
});
