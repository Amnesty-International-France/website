<?php

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_682487476f0cb',
		'title' => 'Catégorie éditoriale',
		'fields' => array(
			array(
				'key' => 'field_68248747c71a5',
				'label' => 'Catégorie éditoriale',
				'name' => 'editorial_category',
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
					'enquetes' => 'Enquêtes',
					'entretiens' => 'Entretiens',
					'portraits' => 'Portraits',
					'rapports' => 'Rapports',
					'temoignages' => 'Témoignages',
					'tribunes' => 'Tribunes',
				),
				'default_value' => false,
				'return_format' => 'array',
				'multiple' => 0,
				'allow_null' => 1,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				),
				array(
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:actualites',
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
		'show_in_rest' => 0,
	) );

	acf_add_local_field_group( array(
		'key' => 'group_6823683e775d5',
		'title' => 'Hero archive page',
		'fields' => array(
			array(
				'key' => 'field_6823683e95956',
				'label' => 'category_image',
				'name' => 'category_image',
				'aria-label' => '',
				'type' => 'image',
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
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
				'allow_in_bindings' => 0,
				'preview_size' => 'medium',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'category',
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
		'show_in_rest' => 0,
	) );

	acf_add_local_field_group( array(
		'key' => 'group_680b40638d861',
		'title' => 'Prismic Import',
		'fields' => array(
			array(
				'key' => 'field_680b40633bb1e',
				'label' => 'prismic_json',
				'name' => 'prismic_json',
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
					'value' => 'post',
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
		'show_in_rest' => 0,
	) );
} );

add_action('acf/save_post', function($post_id) {
	if (get_post_type($post_id) !== 'post') {
		return;
	}

	$categories = wp_get_post_categories($post_id, ['fields' => 'slugs']);

	if (!in_array('actualites', $categories, true)) {
		delete_field('editorial_category', $post_id);
	}
}, 20);
