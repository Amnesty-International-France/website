<?php

declare(strict_types=1);

function amnesty_register_clh_cpt(): void
{
    $labels = [
        'name' => 'Campagne CLH',
        'singular_name' => 'CLH',
        'add_new' => 'Ajouter une campagne CLH',
        'add_new_item' => 'Ajouter une nouvelle campagne CLH',
        'edit_item' => 'Modifier la campagne CLH',
        'new_item' => 'Nouvelle campagne CLH',
        'view_item' => 'Voir la campagne CLH',
        'search_items' => 'Rechercher une campagne CLH',
        'not_found' => 'Aucune campagne CLH trouvée',
        'not_found_in_trash' => 'Aucune campagne CLH dans la corbeille',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 32,
        'menu_icon' => 'dashicons-megaphone',
        'has_archive' => true,
        'rewrite' => ['slug' => 'clh'],
        'supports' => ['title', 'thumbnail', 'custom-fields'],
        'show_in_rest' => false,
    ];

    register_post_type('clh', $args);
}

add_action('init', 'amnesty_register_clh_cpt');

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_6a1590ff05507',
        'title' => 'Campagne CLH',
        'fields' => [
            [
                'key' => 'field_6a159135fec38',
                'label' => 'Date de début',
                'name' => 'start_date_highligth_clh',
                'aria-label' => '',
                'type' => 'date_time_picker',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'display_format' => 'Y-m-d H:i:s',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 1,
                'default_to_current_date' => 0,
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_6a159187fec39',
                'label' => 'Date de fin de campagne',
                'name' => 'end_date_highlight_clh',
                'aria-label' => '',
                'type' => 'date_time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'display_format' => 'Y-m-d H:i:s',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 1,
                'default_to_current_date' => 0,
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_6a1591cefec3a',
                'label' => 'Liste des pétitions CLH',
                'name' => 'list_petition_clh',
                'aria-label' => '',
                'type' => 'relationship',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'post_type' => ['petition'],
                'post_status' => '',
                'taxonomy' => '',
                'filters' => ['search'],
                'return_format' => 'object',
                'min' => '',
                'max' => '',
                'allow_in_bindings' => 0,
                'elements' => '',
                'bidirectional' => 1,
                'bidirectional_target' => '',
            ],
            [
                'key' => 'field_6a16f00bb9903',
                'label' => 'Message à partager',
                'name' => 'message_clh',
                'aria-label' => '',
                'type' => 'textarea',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'rows' => '',
                'placeholder' => '',
                'new_lines' => '',
            ],
            [
                'key' => 'field_6a16f04cb9904',
                'label' => 'Liens de la pétition à partager',
                'name' => 'url_petition_share',
                'aria-label' => '',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'default_value' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
            ],
            [
                'key' => 'field_6a2fb4668185a',
                'label' => 'Objet de l\'email',
                'name' => 'email_object',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => 'Objet de l\'email de partage. Balises disponibles : [titre], [lien].',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6a2fb4928185b',
                'label' => 'Corps du message',
                'name' => 'email_body',
                'aria-label' => '',
                'type' => 'textarea',
                'instructions' => 'Corps de l\'email de partage (remplace le texte par défaut). Balises disponibles : [lien] pour le lien de la pétition à partager, [titre] pour son titre.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'rows' => '',
                'placeholder' => '',
                'new_lines' => '',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'clh',
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
        'display_title' => '',
    ]);

    acf_add_local_field_group([
        'key' => 'group_6a199c4a7e369',
        'title' => 'Highlight',
        'fields' => [
            [
                'key' => 'field_6a199c4b42f0a',
                'label' => 'Passer en mode Temps fort',
                'name' => 'highlight_clh',
                'aria-label' => '',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'message' => '',
                'default_value' => 0,
                'allow_in_bindings' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ],
            [
                'key' => 'field_6a199c9f42f0b',
                'label' => 'Choisir la campagne CLH',
                'name' => 'campaign_clh',
                'aria-label' => '',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_6a199c4b42f0a',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'post_type' => ['clh'],
                'post_status' => ['publish'],
                'taxonomy' => '',
                'filters' => ['search'],
                'return_format' => 'object',
                'min' => '',
                'max' => '',
                'multiple' => false,
                'allow_in_bindings' => 0,
                'elements' => '',
                'bidirectional' => 0,
                'bidirectional_target' => [],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'page_slug',
                    'operator' => '==',
                    'value' => 'changez-leur-histoire',
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
        'display_title' => '',
    ]);
});

add_filter(
    'acf/fields/relationship/query/name=list_petition_clh',
    function (array $args): array {
        $args['meta_query'] = [
            [
                'key' => 'clh_petition',
                'value' => '1',
                'compare' => '=',
            ],
        ];
        return $args;
    }
);
