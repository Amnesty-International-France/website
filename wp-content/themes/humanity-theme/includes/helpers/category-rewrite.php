<?php

function amnesty_custom_category_rewrite_rules($wp_rewrite)
{
    $categories = get_categories(array('hide_empty' => false));
    $new_rules = array();

    foreach ($categories as $category) {
        $slug = $category->slug;

        $new_rules["{$slug}/page/?([0-9]{1,})/?$"] = 'index.php?category_name=' . $slug . '&paged=$matches[1]';

        $new_rules["{$slug}/?$"] = 'index.php?category_name=' . $slug;
    }

    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'amnesty_custom_category_rewrite_rules');


function amnesty_redirect_old_category_urls()
{
    if (is_category() && strpos($_SERVER['REQUEST_URI'], '/category/') !== false) {
        wp_redirect(get_term_link(get_queried_object()), 301);
        exit;
    }
}
add_action('template_redirect', 'amnesty_redirect_old_category_urls');
