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
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'tribe_events',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'fiche_pays',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'landmark',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'local-structures',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'petition',
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
		'key' => 'group_6824a398670a2',
		'title' => 'Sommaire',
		'fields' => array(
			array(
				'key' => 'field_6824a39804731',
				'label' => 'Afficher le sommaire',
				'name' => 'display_toc',
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
				'message' => '',
				'default_value' => 0,
				'allow_in_bindings' => 0,
				'ui' => 0,
				'ui_on_text' => '',
				'ui_off_text' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_taxonomy',
					'operator' => '==',
					'value' => 'category:dossiers',
				),
			),
		),
		'menu_order' => -1,
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
		'key' => 'group_682c408ccdea5',
		'title' => 'Nom de catégorie au singulier',
		'fields' => array(
			array(
				'key' => 'field_682c408d2e95b',
				'label' => 'Nom de catégorie au singulier',
				'name' => 'category_singular_name',
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
		'show_in_rest' => 1,
	) );

	acf_add_local_field_group( array(
		'key' => 'group_685a9d583a748',
		'title' => 'Articles associés',
		'fields' => array(
			array(
				'key' => 'field_685a9d59c62d3',
				'label' => 'Articles associés',
				'name' => '_related_posts_selected',
				'aria-label' => '',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array(
					0 => 'post',
					1 => 'landmark',
				),
				'post_status' => array(
					0 => 'publish',
				),
				'taxonomy' => '',
				'filters' => array(
					0 => 'search',
					1 => 'post_type',
				),
				'return_format' => 'object',
				'min' => '',
				'max' => 3,
				'allow_in_bindings' => 0,
				'elements' => '',
				'bidirectional' => 0,
				'bidirectional_target' => array(
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:actualites',
				),
			),
			array(
				array(
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:chroniques',
				),
			),
			array(
				array(
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:campagnes',
				),
			),
			array(
				array(
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:dossiers',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'landmark',
				),
			),
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
				array(
					'param' => 'page_type',
					'operator' => '!=',
					'value' => 'front_page',
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
	) );

	acf_add_local_field_group(
		array(
			'key'                   => 'group_686fcf4be56a8',
			'title'                 => 'Lien vers la campagne de soutien',
			'fields'                => array(
				array(
					'key'               => 'field_686fcf4cb8a32',
					'label'             => 'Lien vers la campagne de soutien',
					'name'              => 'link-donation',
					'aria-label'        => '',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'maxlength'         => '',
					'allow_in_bindings' => 0,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-fondation',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-don',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 1,
		)
	);

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
					'secretariat-national' => 'Secrétariat national',
					'region' => 'Région',
					'a-distance' => 'À distance',
				),
				'default_value' => 'Secrétariat national',
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
                'key' => 'field_688355a1b2c3d',
                'label' => 'Ville',
                'name' => 'city',
                'aria-label' => '',
                'type' => 'text',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'allow_in_bindings' => 0,
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
					'connaitre-le-mouvement' => 'Connaître le mouvement',
					'approfondir-thematiques-campagnes' => 'Approfondir des thématiques ou des campagnes',
					'acquerir-renforcer-competences' => 'Acquérir ou renforcer des compétences',
					'formations-edh-region' => "Formations dédiées à l'Education aux Droits Humains en région",
					'formations-structure-militante' => 'Formations dédiées au renforcement de sa structure militante',
				),
				'default_value' => array(
					0 => 'Connaître le mouvement',
				),
				'multiple' => 0,
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

	acf_add_local_field_group( array(
		'key' => 'group_6888934b5990e',
		'title' => 'sur-titre',
		'fields' => array(
			array(
				'key' => 'field_6888934b292bb',
				'label' => 'Sur-titre',
				'name' => 'sur-titre',
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
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-fondation',
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
