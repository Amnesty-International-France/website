<?php

declare(strict_types=1);

/**
 * Register Custom Post-Type: Newsletter
 */
function amnesty_register_newsletter_cpt()
{
    $args = [
        'labels'       => [
            'name'               => 'Newsletter',
            'singular_name'      => 'Newsletter',
            'add_new'            => 'Créer une newsletter',
            'add_new_item'       => 'Créer une nouvelle newsletter',
            'edit_item'          => 'Modifier la newsletter',
            'new_item'           => 'Nouvelle newsletter',
            'view_item'          => 'Voir la newsletter',
            'search_items'       => 'Rechercher une newsletter',
            'not_found'          => 'Aucune newsletter trouvée',
            'not_found_in_trash' => 'Aucune newsletter dans la corbeille',
        ],
        'public'       => false,
        'has_archive'  => false,
        'rewrite'      => [ 'slug' => 'newsletter' ],
        'supports'     => [ 'title', 'custom-fields' ],
        'show_ui'      => true,
        'show_in_menu' => true,
        'show_in_rest' => false,
        'menu_icon'    => 'dashicons-email-alt',
    ];
    register_post_type('newsletter', $args);
}
add_action('init', 'amnesty_register_newsletter_cpt');

add_action('acf/include_fields', function () {
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_68d3e6096f617',
        'title' => 'newsletter',
        'fields' => [
            [
                'key' => 'field_68d5044efa13c',
                'label' => '1',
                'name' => '1',
                'aria-label' => '',
                'type' => 'group',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_68d50678fa13f',
                        'label' => 'Accroche',
                        'name' => 'accroche',
                        'aria-label' => '',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'rows' => '',
                        'placeholder' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_68d50683fa140',
                        'label' => 'Libellé du lien',
                        'name' => 'libelle_du_lien',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ],
                    [
                        'key' => 'field_68d5069ffa141',
                        'label' => 'Lien',
                        'name' => 'lien',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'post_type' => [
                            0 => 'landmark',
                            1 => 'post',
                            2 => 'petition',
                        ],
                        'post_status' => [
                            0 => 'publish',
                        ],
                        'taxonomy' => '',
                        'return_format' => 'object',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => [
                        ],
                    ],
                ],
            ],
            [
                'key' => 'field_68d509a7f5df8',
                'label' => '2',
                'name' => '2',
                'aria-label' => '',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_68d509a7f5dfd',
                        'label' => 'Accroche',
                        'name' => 'accroche',
                        'aria-label' => '',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'rows' => '',
                        'placeholder' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_68d509a7f5dfe',
                        'label' => 'Libellé du lien',
                        'name' => 'libelle_du_lien',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ],
                    [
                        'key' => 'field_68d509a7f5dff',
                        'label' => 'Lien',
                        'name' => 'lien',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'post_type' => [
                            0 => 'landmark',
                            1 => 'post',
                            2 => 'petition',
                        ],
                        'post_status' => [
                            0 => 'publish',
                        ],
                        'taxonomy' => '',
                        'return_format' => '',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => [
                        ],
                    ],
                ],
            ],
            [
                'key' => 'field_68d509aaf5e00',
                'label' => '3',
                'name' => '3',
                'aria-label' => '',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_68d509aaf5e05',
                        'label' => 'Accroche',
                        'name' => 'accroche',
                        'aria-label' => '',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'rows' => '',
                        'placeholder' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_68d509aaf5e06',
                        'label' => 'Libellé du lien',
                        'name' => 'libelle_du_lien',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ],
                    [
                        'key' => 'field_68d509aaf5e07',
                        'label' => 'Lien',
                        'name' => 'lien',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'post_type' => [
                            0 => 'landmark',
                            1 => 'post',
                            2 => 'petition',
                        ],
                        'post_status' => [
                            0 => 'publish',
                        ],
                        'taxonomy' => '',
                        'return_format' => '',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => [
                        ],
                    ],
                ],
            ],
            [
                'key' => 'field_68d509acf5e08',
                'label' => '4',
                'name' => '4',
                'aria-label' => '',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_68d509acf5e0d',
                        'label' => 'Accroche',
                        'name' => 'accroche',
                        'aria-label' => '',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'rows' => '',
                        'placeholder' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_68d509acf5e0e',
                        'label' => 'Libellé du lien',
                        'name' => 'libelle_du_lien',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ],
                    [
                        'key' => 'field_68d509acf5e0f',
                        'label' => 'Lien',
                        'name' => 'lien',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'post_type' => [
                            0 => 'landmark',
                            1 => 'post',
                            2 => 'petition',
                        ],
                        'post_status' => [
                            0 => 'publish',
                        ],
                        'taxonomy' => '',
                        'return_format' => '',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => [
                        ],
                    ],
                ],
            ],
            [
                'key' => 'field_68d509adf5e10',
                'label' => '5',
                'name' => '5',
                'aria-label' => '',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key' => 'field_68d509adf5e15',
                        'label' => 'Accroche',
                        'name' => 'accroche',
                        'aria-label' => '',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'rows' => '',
                        'placeholder' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_68d509adf5e16',
                        'label' => 'Libellé du lien',
                        'name' => 'libelle_du_lien',
                        'aria-label' => '',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'maxlength' => '',
                        'allow_in_bindings' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                    ],
                    [
                        'key' => 'field_68d509adf5e17',
                        'label' => 'Lien',
                        'name' => 'lien',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'post_type' => [
                            0 => 'landmark',
                            1 => 'post',
                            2 => 'petition',
                        ],
                        'post_status' => [
                            0 => 'publish',
                        ],
                        'taxonomy' => '',
                        'return_format' => 'object',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => [
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'newsletter',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ]);
});

function amnesty_newsletter_preview_button(WP_Post $post)
{
    if ($post->post_type === 'newsletter' && $post->post_status === 'publish') {
        $url = admin_url('admin.php?page=newsletter_preview&newsletter_id=' . $post->ID);
        echo '<div class="misc-pub-section">';
        echo '<a href="' . esc_url($url) . '" target="_blank" class="button button-primary">Voir la newsletter</a>';
        echo '</div>';
    }
}
add_action('post_submitbox_misc_actions', 'amnesty_newsletter_preview_button');

function amnesty_newsletter_register_preview_page()
{
    add_submenu_page(
        null,
        'Preview newsletter',
        'Preview newsletter',
        'edit_posts',
        'newsletter_preview',
        'amnesty_newsletter_render_preview_page'
    );
}
add_action('admin_menu', 'amnesty_newsletter_register_preview_page');

function amnesty_newsletter_render_preview_page()
{
    include get_template_directory() . '/page-newsletter.php';
}
