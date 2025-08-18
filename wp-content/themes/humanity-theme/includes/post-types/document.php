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
			'public' => false,
			'has_archive' => false,
			'rewrite' => array('slug' => 'documents'),
			'supports' => array('title', 'thumbnail', 'custom-fields', 'excerpt'),
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

add_action('init', 'amnesty_register_document_cpt');

add_action( 'acf/include_fields', function() {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(array(
		'key' => 'group_688c7477a4701',
		'title' => 'Document',
		'fields' => array(
			array(
				'key' => 'field_688c7478cfe59',
				'label' => 'Upload du document',
				'name' => 'upload_du_document',
				'aria-label' => '',
				'type' => 'file',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'library' => 'all',
				'min_size' => '',
				'max_size' => '',
				'mime_types' => '',
				'allow_in_bindings' => 0,
			),
            array(
                'key' => 'field_689a05696c83f',
                'label' => 'Type libre',
                'name' => 'type_libre',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_689a05696c84f',
                'label' => 'AI Index',
                'name' => 'ai_index',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'document',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 1,
	));
});
