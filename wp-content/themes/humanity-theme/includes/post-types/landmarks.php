<?php
/**
 * Register Custom Post Type: Repère
 */

function amnesty_register_landmarks_cpt() {
    register_post_type('landmark',
        array(
            'labels' => array(
                'name' => 'Repères',
                'singular_name' => 'Repère',
                'add_new' => 'Ajouter un Repère',
                'add_new_item' => 'Ajouter un nouveau Repère',
                'edit_item' => 'Modifier le Repère',
                'new_item' => 'Nouveau Repère',
                'view_item' => 'Voir le Repère',
                'search_items' => 'Rechercher un Repère',
                'not_found' => 'Aucune Repère trouvé',
                'not_found_in_trash' => 'Aucun Repère dans la corbeille'
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'reperes'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'menu_icon' => 'dashicons-admin-page',
            'show_in_rest' => true,
            'taxonomies' => array('combat', 'landmark_category'),
        )
    );
}
add_action('init', 'amnesty_register_landmarks_cpt');

/**
 * Register Custom Taxonomy : Landmark Category
 */
function amnesty_register_landmark_category_taxonomy() {
    register_taxonomy('landmark_category', 'landmark', array(
        'labels' => array(
            'name' => 'Catégories de Repères',
            'singular_name' => 'Catégorie de Repère',
            'search_items' => 'Rechercher des catégories',
            'all_items' => 'Toutes les catégories',
            'edit_item' => 'Modifier la catégorie',
            'update_item' => 'Mettre à jour la catégorie',
            'add_new_item' => 'Ajouter une nouvelle catégorie',
            'new_item_name' => 'Nom de la nouvelle catégorie',
            'menu_name' => 'Catégories Repères',
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'landmark_category'),
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
    ));
}
add_action('init', 'amnesty_register_landmark_category_taxonomy');
