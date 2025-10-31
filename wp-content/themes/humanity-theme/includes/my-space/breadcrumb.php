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
            return [
                [
                    'text' => get_the_title($page_mon_espace),
                    'url'  => get_permalink($page_mon_espace),
                ],
                [
                    'text' => get_the_title($page_boite_a_outils),
                    'url'  => get_permalink($page_boite_a_outils),
                ],
                [
                    'text' => get_the_title($page_se_former),
                    'url'  => get_permalink($page_se_former),
                ],
                [
                    'text' => get_the_title($post),
                ],
            ];
        }
    }

    if (is_singular('petition') && get_query_var('is_my_space_petition')) {
        $page_mon_espace = get_page_by_path('mon-espace');
        $page_agir_et_se_mobiliser = get_page_by_path('mon-espace/agir-et-se-mobiliser');
        $page_nos_petitions = get_page_by_path('mon-espace/agir-et-se-mobiliser/nos-petitions');

        if ($page_mon_espace && $page_agir_et_se_mobiliser && $page_nos_petitions) {
            return [
                [
                    'text' => get_the_title($page_mon_espace),
                    'url'  => get_permalink($page_mon_espace),
                ],
                [
                    'text' => get_the_title($page_agir_et_se_mobiliser),
                    'url'  => get_permalink($page_agir_et_se_mobiliser),
                ],
                [
                    'text' => get_the_title($page_nos_petitions),
                    'url'  => get_permalink($page_nos_petitions),
                ],
                [
                    'text' => get_the_title($post),
                ],
            ];
        }
    }

    if (is_singular('post') && get_query_var('is_my_space_actualites')) {
        $page_mon_espace = get_page_by_path('mon-espace');
        $page_actualites = get_page_by_path('mon-espace/actualites');

        if ($page_mon_espace && $page_actualites) {
            return [
                [
                    'text' => get_the_title($page_mon_espace),
                    'url'  => get_permalink($page_mon_espace),
                ],
                [
                    'text' => get_the_title($page_actualites),
                    'url'  => get_permalink($page_actualites),
                ],
                [
                    'text' => get_the_title($post),
                ],
            ];
        }
    }

    $page_mon_espace = get_page_by_path('mon-espace');

    if (
        $page_mon_espace &&
        is_page() &&
        strpos(get_permalink($post), '/mon-espace/') !== false
    ) {
        $ancestors = get_post_ancestors($post);

        if (in_array($page_mon_espace->ID, $ancestors, true)) {
            array_shift($links);
            return $links;
        }
    }

    return $links;
}
