<?php

declare(strict_types=1);

/**
 * Register Custom Post-Type: Alert Banner
 */
function amnesty_register_alert_banner_cpt(): void
{
	if (! amnesty_feature_is_enabled('alert-banner')) {
		return;
	}

	register_post_type(
		'alert-banner',
		[
			'labels'              => [
				'name'               => 'Bandeau d\'alerte',
				'singular_name'      => 'Bandeau d\'alerte',
				'add_new'            => 'Ajouter un Bandeau d\'alerte',
				'add_new_item'       => 'Ajouter un nouveau Bandeau d\'alerte',
				'edit_item'          => 'Modifier le Bandeau d\'alerte',
				'new_item'           => 'Nouveau Bandeau d\'alerte',
				'view_item'          => 'Voir le Bandeau d\'alerte',
				'search_items'       => 'Rechercher un Bandeau d\'alerte',
				'not_found'          => 'Aucun Bandeau d\'alerte trouvé',
				'not_found_in_trash' => 'Aucun Bandeau d\'alerte dans la corbeille',
			],
			'supports'            => [ 'title', 'thumbnail', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 46,
			'menu_icon'           => 'dashicons-megaphone',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
			'rewrite'             => false,
			'codename'            => 'alert-banner',
		]
	);
}

add_action('init', 'amnesty_register_alert_banner_cpt');

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_69e8b4daac200',
		'title' => 'Bandeau d\'alerte',
		'fields' => array(
			array(
				'key' => 'field_69e8b4db5da26',
				'label' => 'Description / accroche',
				'name' => 'description',
				'aria-label' => '',
				'type' => 'textarea',
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
				'rows' => '',
				'placeholder' => '',
				'new_lines' => '',
			),
			array(
				'key' => 'field_69e8b5065da27',
				'label' => 'Url du lien',
				'name' => 'url',
				'aria-label' => '',
				'type' => 'url',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_69e8b54d5da28',
				'label' => 'Label du CTA',
				'name' => 'label_cta',
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
					'value' => 'alert-banner',
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
		'display_title' => '',
	) );
} );

