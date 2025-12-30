<?php

declare(strict_types=1);

/**
 * Register Custom Post Type: Portrait
 */
function amnesty_register_portrait_cpt(): void
{
    register_post_type(
        'portrait',
        [
            'labels'              => [
                'name'               => 'Portraits',
                'singular_name'      => 'Portrait',
                'add_new'            => 'Ajouter un Portrait',
                'add_new_item'       => 'Ajouter un nouveau Portrait',
                'edit_item'          => 'Modifier un Portrait',
                'new_item'           => 'Nouveau Portrait',
                'view_item'          => 'Voir le Portrait',
                'search_items'       => 'Rechercher un Portrait',
                'not_found'          => 'Aucun Portrait trouvé',
                'not_found_in_trash' => 'Aucun Portrait dans la corbeille',
            ],
            'public'              => true,
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'agir-avec-nous/portraits' ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'menu_icon'           => 'dashicons-groups',
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 24,
        ]
    );
}

add_action('init', 'amnesty_register_portrait_cpt');


add_filter('wpseo_breadcrumb_links', 'amnesty_custom_portrait_breadcrumbs');

function amnesty_custom_portrait_breadcrumbs($links)
{
    if (is_singular('portrait') || is_post_type_archive('portrait')) {

        $new_parents = [
            [
                'url'  => home_url('/agir-avec-nous/'),
                'text' => 'Agir avec nous',
            ],
        ];

        array_splice($links, 1, 0, $new_parents);
    }

    return $links;
}
