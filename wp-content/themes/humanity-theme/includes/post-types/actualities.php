<?php

function aif_myspace_actualites_rewrite_rules()
{
    add_filter('query_vars', function ($vars) {
        $vars[] = 'is_my_space_actualites';
        return $vars;
    });

    add_rewrite_rule(
        '^mon-espace/actualites/([^/]+)/?$',
        'index.php?category_name=actualites&name=$matches[1]&is_my_space_actualites=1',
        'top'
    );
}
add_action('init', 'aif_myspace_actualites_rewrite_rules');

function aif_filter_actualites_permalink_in_myspace($permalink, $post)
{
    if (!empty($GLOBALS['is_my_space_actualites_loop']) && has_term('actualites', 'category', $post)) {
        $permalink = home_url('/mon-espace/actualites/' . $post->post_name . '/');
    }
    return $permalink;
}
add_filter('post_link', 'aif_filter_actualites_permalink_in_myspace', 10, 2);

function aif_myspace_actualites_template_include($template)
{
    if (get_query_var('is_my_space_actualites') && is_singular('post')) {
        $new_template = get_stylesheet_directory() . '/patterns/single-actuality-my-space.php';

        if (file_exists($new_template)) {
            return $new_template;
        }
    }

    return $template;
}
add_filter('template_include', 'aif_myspace_actualites_template_include', 99);
