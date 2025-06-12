<?php
/**
 * Register Custom Post Type: Pays
 */

function amnesty_register_countries_cpt() {
    register_post_type('fiche_pays',
        array(
            'labels' => array(
                'name' => 'Pays',
                'singular_name' => 'Pays',
                'add_new' => 'Ajouter un Pays',
                'add_new_item' => 'Ajouter un nouveau Pays',
                'edit_item' => 'Modifier un Pays',
                'new_item' => 'Nouveau Pays',
                'view_item' => 'Voir le Pays',
                'search_items' => 'Rechercher un Pays',
                'not_found' => 'Aucun Pays trouvé',
                'not_found_in_trash' => 'Aucun Pays dans la corbeille'
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'pays'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-admin-page',
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'amnesty_register_countries_cpt');
