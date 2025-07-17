<?php

declare(strict_types=1);

/**
 * Register Custom Post Type: Press Release
 */
function amnesty_register_press_release_cpt() {
	register_post_type(
		'press-release',
		[
			'labels'       => [
				'name'               => 'Communiqués',
				'singular_name'      => 'Communiqué',
				'add_new'            => 'Ajouter un Communiqué',
				'add_new_item'       => 'Ajouter un nouveau Communiqué',
				'edit_item'          => 'Modifier le Communiqué',
				'new_item'           => 'Nouveau Communiqué',
				'view_item'          => 'Voir le Communiqué',
				'search_items'       => 'Rechercher un Communiqué',
				'not_found'          => 'Aucune Communiqué trouvé',
				'not_found_in_trash' => 'Aucun Communiqué dans la corbeille',
			],
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'communiques' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'menu_icon'    => 'dashicons-admin-page',
			'show_in_rest' => true,
		]
	);
}

add_action( 'init', 'amnesty_register_press_release_cpt' );
