<?php
/**
 * Register Custom Post Type: Fiche Pays
 */

function amnesty_register_countries_cpt() {
    register_post_type('fiche_pays',
        array(
            'labels' => array(
                'name' => 'Fiches Pays',
                'singular_name' => 'Fiche Pays',
                'add_new' => 'Ajouter une Fiche Pays',
                'add_new_item' => 'Ajouter une nouvelle Fiche Pays',
                'edit_item' => 'Modifier la Fiche Pays',
                'new_item' => 'Nouvelle Fiche Pays',
                'view_item' => 'Voir la Fiche Pays',
                'search_items' => 'Rechercher une Fiche Pays',
                'not_found' => 'Aucune Fiche Pays trouvée',
                'not_found_in_trash' => 'Aucune Fiche Pays dans la corbeille'
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'pays'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-admin-page',
            'show_in_rest' => true,
            'taxonomies' => array('location'),
        )
    );
}
add_action('init', 'amnesty_register_countries_cpt');
