<?php

declare(strict_types=1);

/**
 * Register Custom Post Type: Press Release
 */
function amnesty_register_press_release_cpt()
{
    register_post_type(
        'press-release',
        [
            'labels'       => [
                'name'               => 'Communiqués de presse',
                'singular_name'      => 'Communiqué de presse',
                'add_new'            => 'Ajouter un Communiqué de presse',
                'add_new_item'       => 'Ajouter un nouveau Communiqué de presse',
                'edit_item'          => 'Modifier le Communiqué de presse',
                'new_item'           => 'Nouveau Communiqué de presse',
                'view_item'          => 'Voir le Communiqué de presse',
                'search_items'       => 'Rechercher un Communiqué de presse',
                'not_found'          => 'Aucun Communiqué de presse trouvé',
                'not_found_in_trash' => 'Aucun Communiqué de presse dans la corbeille',
            ],
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => [ 'slug' => 'communiques' ],
            'supports'     => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'menu_icon'    => 'dashicons-admin-page',
            'show_in_rest' => true,
        ]
    );
}

add_action('init', 'amnesty_register_press_release_cpt');
