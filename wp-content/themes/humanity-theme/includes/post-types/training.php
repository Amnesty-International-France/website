<?php

declare(strict_types=1);

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
		)
	);
}

add_action('init', 'amnesty_register_trainings_cpt');

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_6883319088d12',
		'title' => 'Formation',
		'fields' => array(
			array(
				'key' => 'field_6883319051ddc',
				'label' => 'Lieu de formation',
				'name' => 'lieu',
				'aria-label' => '',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'Siège' => 'Siège',
					'Région' => 'Région',
					'À distance' => 'À distance',
				),
				'default_value' => 'Siège',
				'return_format' => 'value',
				'multiple' => 0,
				'allow_null' => 0,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'create_options' => 0,
				'save_options' => 0,
			),
			array(
				'key' => 'field_688334da51ddd',
				'label' => 'Date de la formation',
				'name' => 'date',
				'aria-label' => '',
				'type' => 'date_time_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'd/m/Y g:i a',
				'return_format' => 'd/m/Y g:i a',
				'first_day' => 1,
				'allow_in_bindings' => 0,
			),
			array(
				'key' => 'field_68833cec4b6fa',
				'label' => 'Réservé aux membres',
				'name' => 'members_only',
				'aria-label' => '',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => 'Réservé aux membres',
				'default_value' => 0,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
			array(
				'key' => 'field_688344d2380a3',
				'label' => 'Catégories',
				'name' => 'categories',
				'aria-label' => '',
				'type' => 'checkbox',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'Cat 1' => 'Cat 1',
					'Cat 2' => 'Cat 2',
					'Cat 3' => 'Cat 3',
				),
				'default_value' => array(
					0 => 'Cat 1',
				),
				'return_format' => 'value',
				'allow_custom' => 0,
				'allow_in_bindings' => 0,
				'layout' => 'vertical',
				'toggle' => 0,
				'save_custom' => 0,
				'custom_choice_button_text' => 'Ajouter un nouveau choix',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'training',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'side',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 1,
	) );
} );
