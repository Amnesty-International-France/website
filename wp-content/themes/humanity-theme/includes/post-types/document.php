<?php

declare(strict_types=1);

/**
 * Register Custom Post-Type: Document
 */
function amnesty_register_document_cpt(): void
{
	register_post_type(
		'document',
		array(
			'labels' => array(
				'name' => 'Documents',
				'singular_name' => 'Document',
				'add_new' => 'Ajouter un Document',
				'add_new_item' => 'Ajouter un nouveau Document',
				'edit_item' => 'Modifier le Document',
				'new_item' => 'Nouveau Document',
				'view_item' => 'Voir le Document',
				'search_items' => 'Rechercher un Document',
				'not_found' => 'Aucun Document trouvé',
				'not_found_in_trash' => 'Aucun Document dans la corbeille',
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'documents'),
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
			'menu_icon' => 'dashicons-admin-page',
			'show_in_rest' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,
		)
	);
}

add_action('init', 'amnesty_register_document_cpt');
