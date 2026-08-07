<?php

function tax_location_rewrite_rules()
{
    add_rewrite_rule(
        '^categorie/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?location=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^categorie/([^/]+)/?$',
        'index.php?location=$matches[1]',
        'top'
    );
}
add_action('init', 'tax_location_rewrite_rules');

/**
 * Point location term links at the URL the rules above actually serve.
 *
 * The taxonomy is registered with rewrite => ['slug' => 'pays'], the same base as
 * the fiche_pays post type. The post type wins that route (its permastruct is
 * added first, see the include order in functions.php), so get_term_link()
 * returns /pays/{slug}/, which renders the country sheet rather than the term
 * archive. The archive only exists at /categorie/{slug}/, which is why the
 * templates were building that link by hand.
 *
 * Doing it here instead of at each call site also gives Yoast the right value for
 * the XML sitemap and for the canonical of /categorie/{slug}/.
 */
add_filter('term_link', function (string $link, WP_Term $term, string $taxonomy): string {
    if ('location' !== $taxonomy) {
        return $link;
    }

    return home_url("/categorie/{$term->slug}/");
}, 10, 3);
