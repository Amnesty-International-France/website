<?php

add_filter('wpseo_breadcrumb_links', 'my_space_custom_breadcrumb');

function my_space_custom_breadcrumb($links)
{
    global $post;

    if (! isset($post)) {
        return $links;
    }

    if (is_singular('training') && get_query_var('is_my_space_training')) {
        $page_mon_espace = get_page_by_path('mon-espace');
        $page_boite_a_outils = get_page_by_path('mon-espace/boite-a-outils');
        $page_se_former = get_page_by_path('mon-espace/boite-a-outils/se-former');

        if ($page_mon_espace && $page_boite_a_outils && $page_se_former) {
            $new_breadcrumb = [];
            $new_breadcrumb[] = [
                'text' => get_the_title($page_mon_espace),
                'url'  => get_permalink($page_mon_espace),
            ];
            $new_breadcrumb[] = [
                'text' => get_the_title($page_boite_a_outils),
                'url'  => get_permalink($page_boite_a_outils),
            ];
            $new_breadcrumb[] = [
                'text' => get_the_title($page_se_former),
                'url'  => get_permalink($page_se_former),
            ];
            $new_breadcrumb[] = [
                'text' => get_the_title($post),
            ];
            return $new_breadcrumb;
        }
    }

    if (is_singular('post') && get_query_var('is_my_space_actualites')) {
        $page_mon_espace = get_page_by_path('mon-espace');
        $page_actualites = get_page_by_path('mon-espace/actualites');

        if ($page_mon_espace && $page_actualites) {
            $new_breadcrumb = [];

            $new_breadcrumb[] = [
                'text' => get_the_title($page_mon_espace),
                'url'  => get_permalink($page_mon_espace),
            ];

            $new_breadcrumb[] = [
                'text' => get_the_title($page_actualites),
                'url'  => get_permalink($page_actualites),
            ];

            $new_breadcrumb[] = [
                'text' => get_the_title($post),
            ];

            return $new_breadcrumb;
        }
    }

    $page_mon_espace = get_page_by_path('mon-espace');
    if ($page_mon_espace && is_page() && in_array($page_mon_espace->ID, get_post_ancestors($post))) {
        array_shift($links);
        return $links;
    }

    return $links;
}
