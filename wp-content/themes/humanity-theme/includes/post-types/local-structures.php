<?php

function amnesty_register_local_structures_cpt() {
	$labels = [
		'name' => 'Structures locales',
		'singular_name' => 'Structure locale',
		'add_new' => 'Ajouter une Structure locale',
		'add_new_item' => 'Ajouter une nouvelle Structure locale',
		'edit_item' => 'Modifier une Structure locale',
		'new_item' => 'Nouvelle Structure locale',
		'view_item' => 'Voir la Structure locale',
		'search_items' => 'Rechercher une Structure locale',
		'not_found' => 'Aucune Structure locale trouvée',
		'not_found_in_trash' => 'Aucune Structure locale dans la corbeille'
	];
	$args = [
		'labels' => $labels,
		'public' => true,
		'has_archive' => true,
		'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
		'menu_icon' => 'dashicons-admin-page',
		'show_in_rest' => true,
	];

	register_post_type( 'local-structures', $args);
}

add_action( 'init', 'amnesty_register_local_structures_cpt' );

add_action( 'acf/include_fields', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key' => 'group_684aca1661799',
        'title' => 'Détails Structure locale',
        'fields' => array(
            array(
                'key' => 'field_684aca163aa91',
                'label' => 'Latitude',
                'name' => 'latitude',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'min' => '',
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_684aca6a3aa92',
                'label' => 'Longitude',
                'name' => 'longitude',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'min' => '',
                'max' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_684aca733aa93',
                'label' => 'Adresse',
                'name' => 'adresse',
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
                'key' => 'field_684aca883aa94',
                'label' => 'Ville',
                'name' => 'ville',
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
                'key' => 'field_684acabd3aa95',
                'label' => 'Téléphone',
                'name' => 'telephone',
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
                'key' => 'field_684acadd3aa96',
                'label' => 'Email',
                'name' => 'email',
                'aria-label' => '',
                'type' => 'email',
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
                'prepend' => '',
                'append' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'local-structures',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 1,
    ) );
} );
