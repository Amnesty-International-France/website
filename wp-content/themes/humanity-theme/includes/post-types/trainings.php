<?php
/**
 * Register Custom Post-Type: Training
 */
function amnesty_register_trainings_cpt(): void
{
	register_post_type(
		'training',
		array(
			'labels' => array(
				'name' => 'Formations',
				'singular_name' => 'Formation',
				'add_new' => 'Ajouter une Formation',
				'add_new_item' => 'Ajouter une nouvelle Formation',
				'edit_item' => 'Modifier la Formation',
				'new_item' => 'Nouvelle Formation',
				'view_item' => 'Voir la Formation',
				'search_items' => 'Rechercher une Formation',
				'not_found' => 'Aucune Formation trouvée',
				'not_found_in_trash' => 'Aucune Formation dans la corbeille',
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'formations'),
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
			'menu_icon' => 'dashicons-admin-page',
			'show_in_rest' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,		
			)
	);
}

add_action('init', 'amnesty_register_trainings_cpt');

