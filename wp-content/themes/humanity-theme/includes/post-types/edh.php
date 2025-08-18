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

add_action( 'acf/include_fields', function() {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group( array(
		'key' => 'group_6892103a7d1b5',
		'title' => 'Catégories',
		'fields' => array(
			array(
				'key' => 'field_6892103b14459',
				'label' => 'Type de contenu',
				'name' => 'type_de_contenu',
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
					'Quiz' => 'Quiz',
					'Livret pédagogique' => 'Livret pédagogique',
					'Séquence pédagogique' => 'Séquence pédagogique',
					'Activité pédagogique' => 'Activité pédagogique',
					'Fiche de lecture' => 'Fiche de lecture',
				),
				'default_value' => 'Quiz',
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
				'key' => 'field_689210a11445a',
				'label' => 'Thème',
				'name' => 'theme',
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
					'Droits et textes fondamentaux' => 'Droits et textes fondamentaux',
					'Liberté d\'expression et droit de manifester' => 'Liberté d\'expression et droit de manifester',
					'Lutte contre les discriminations' => 'Lutte contre les discriminations',
					'Lutte contre les discours toxiques' => 'Lutte contre les discours toxiques',
					'Abolition de la peine de mort' => 'Abolition de la peine de mort',
					'Droits de l\'enfant' => 'Droits de l\'enfant',
					'Droits des femmes' => 'Droits des femmes',
					'Droits des personnes réfugiées et migrantes' => 'Droits des personnes réfugiées et migrantes',
					'Droits des personnes LGBTI+' => 'Droits des personnes LGBTI+',
					'Climat et droits humains' => 'Climat et droits humains',
				),
				'default_value' => 'Droits et textes fondamentaux',
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
				'key' => 'field_689210be1445b',
				'label' => 'Besoins',
				'name' => 'requirements',
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
					'Se familiariser' => 'Se familiariser',
					'Approfondir' => 'Approfondir',
					'À voir, à lire' => 'À voir, à lire',
					'Aller plus loin' => 'Aller plus loin',
				),
				'default_value' => 'Se familiariser',
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
				'key' => 'field_689210f30c5c4',
				'label' => 'Durée de l\'activité',
				'name' => 'activity_duration',
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
					'moins d\'une heure' => 'moins d\'une heure',
					'plus d\'une heure' => 'plus d\'une heure',
				),
				'default_value' => 'moins d\'une heure',
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
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'edh',
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
});
