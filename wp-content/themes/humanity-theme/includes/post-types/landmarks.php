<?php
/**
 * Register Custom Post Type: Landmark
 */
function amnesty_register_landmarks_cpt() {
	register_post_type(
		'landmark',
		array(
			'labels'              => array(
				'name'               => 'Repères',
				'singular_name'      => 'Repère',
				'add_new'            => 'Ajouter un Repère',
				'add_new_item'       => 'Ajouter un nouveau Repère',
				'edit_item'          => 'Modifier le Repère',
				'new_item'           => 'Nouveau Repère',
				'view_item'          => 'Voir le Repère',
				'search_items'       => 'Rechercher un Repère',
				'not_found'          => 'Aucune Repère trouvé',
				'not_found_in_trash' => 'Aucun Repère dans la corbeille',
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'reperes' ),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'menu_icon'           => 'dashicons-admin-page',
			'show_in_rest'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
		)
	);
}
add_action( 'init', 'amnesty_register_landmarks_cpt' );
