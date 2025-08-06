<?php

declare(strict_types=1);

/**
 * Register Custom Post Type: Chronicles
 */
function amnesty_register_chronicles_cpt() {
	register_post_type(
		'chronicles',
		[
			'labels'       => [
				'name'               => 'Chroniques',
				'singular_name'      => 'Chronique',
				'add_new'            => 'Ajouter une Chronique',
				'add_new_item'       => 'Ajouter une nouvelle Chronique',
				'edit_item'          => 'Modifier la Chronique',
				'new_item'           => 'Nouvelle Chronique',
				'view_item'          => 'Voir la Chronique',
				'search_items'       => 'Rechercher une Chronique',
				'not_found'          => 'Aucune Chronique trouvée',
				'not_found_in_trash' => 'Aucune Chronique dans la corbeille',
			],
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'chroniques' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'menu_icon'    => 'dashicons-admin-page',
			'show_in_rest' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,
		]
	);
}

add_action( 'init', 'amnesty_register_chronicles_cpt' );

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_689d9ee82e987',
		'title' => 'Hero large page "La chronique"',
		'fields' => array(
			array(
				'key' => 'field_689d9eeeea0f6',
				'label' => 'Sur-titre',
				'name' => 'overtitle',
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
				'default_value' => 'La chronique',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_689d9fc4ea0f7',
				'label' => 'Texte du bouton de lien',
				'name' => 'btn_link_text',
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
				'default_value' => 'Abonnez-vous pour 3€/mois',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array(
				'key' => 'field_689da0c9ea0f8',
				'label' => 'Lien du boutton',
				'name' => 'btn_link',
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
				'default_value' => 'https://soutenir.amnesty.fr/b?cid=365&lang=fr_FR&reserved_originecode=null',
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
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-the-chronicle-promo',
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
		'key' => 'group_689dadd09f8fa',
		'title' => 'Chapo page "La chronique"',
		'fields' => array(
			array(
				'key' => 'field_689dadd67591e',
				'label' => 'Texte du chapo',
				'name' => 'chapo_text',
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
				'default_value' => 'La Chronique, c’est LE magazine des droits humains.

Chaque mois, des journalistes enquêtent sur des sujets liés aux droits humains.',
				'maxlength' => '',
				'allow_in_bindings' => 0,
				'rows' => '',
				'placeholder' => '',
				'new_lines' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-the-chronicle-promo',
				),
			),
		),
		'menu_order' => 1,
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
