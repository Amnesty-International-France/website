<?php

/**
 * Register Custom Post Type: Actu mon espace
 */
function amnesty_register_actualities_my_space_cpt()
{
    register_post_type(
        'actualities-my-space',
        [
            'labels'       => [
                'name'               => 'Actu mon espace',
                'singular_name'      => 'Actu mon espace',
                'add_new'            => 'Ajouter une actu mon espace',
                'add_new_item'       => 'Ajouter une nouvelle actu mon espace',
                'edit_item'          => 'Modifier une actu mon espace',
                'new_item'           => 'Nouvelle actu mon espace',
                'view_item'          => 'Voir l\'actu mon espace',
                'search_items'       => 'Rechercher une actu mon espace',
                'not_found'          => 'Aucune actu mon espace trouvée',
                'not_found_in_trash' => 'Aucune actu mon espace dans la corbeille',
            ],
            'capability_type' => 'post',
            'public'       => true,
            'has_archive'  => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_position' => 4,
            'rewrite'      => [ 'slug' => 'mon-espace/actualites' ],
            'supports'     => [ 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions' ],
            'menu_icon'    => 'dashicons-admin-post',
            'show_in_rest' => true,
        ]
    );
}
add_action('init', 'amnesty_register_actualities_my_space_cpt');

/**
 * Route the pagination of the "mon-espace/actualites" page.
 */
function amnesty_actualities_my_space_pagination_rewrite_rule(): void
{
    $regex = '^mon-espace/actualites/page/?([0-9]{1,})/?$';
    add_rewrite_rule(
        $regex,
        'index.php?pagename=mon-espace/actualites&paged=$matches[1]',
        'top'
    );

    $rules = get_option('rewrite_rules');
    if (is_array($rules) && ! isset($rules[ $regex ])) {
        flush_rewrite_rules(false);
    }
}
add_action('init', 'amnesty_actualities_my_space_pagination_rewrite_rule', 20);

add_action('acf/include_fields', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_68d69327a57e4',
        'title' => 'Informations',
        'fields' => [
            [
                'key' => 'field_68d69328f3dae',
                'label' => 'Catégorie',
                'name' => 'category',
                'aria-label' => '',
                'type' => 'select',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [
                    'vie-militante' => 'Vie militante',
                    'vie-democratique' => 'Vie démocratique',
                ],
                'default_value' => false,
                'return_format' => 'value',
                'multiple' => 0,
                'allow_null' => 0,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ajax' => 0,
                'placeholder' => '',
                'create_options' => 0,
                'save_options' => 0,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'actualities-my-space',
                ],
            ],
        ],
        'position' => 'side',
        'menu_order' => 0,
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ]);
});
