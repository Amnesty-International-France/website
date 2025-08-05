<?php

declare(strict_types=1);


/**
 * Register Custom Post Type: EDH
 */
function amnesty_register_edh_cpt()
{
	register_post_type(
		'edh',
		array(
			'labels' => array(
				'name' => 'EDH',
				'singular_name' => 'EDH',
				'add_new' => 'Ajouter un EDH',
				'add_new_item' => 'Ajouter un nouveau EDH',
				'edit_item' => 'Modifier un EDH',
				'new_item' => 'Nouveau EDH',
				'view_item' => 'Voir le EDH',
				'search_items' => 'Rechercher un EDH',
				'not_found' => 'Aucun EDH trouvé',
				'not_found_in_trash' => 'Aucun EDH dans la corbeille',
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'edh'),
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

add_action('init', 'amnesty_register_edh_cpt');
