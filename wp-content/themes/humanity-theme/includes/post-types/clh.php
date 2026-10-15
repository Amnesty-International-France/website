<?php

declare(strict_types=1);

if (!defined('AMNESTY_CLH_START_DATE_FIELD_KEY')) {
    define('AMNESTY_CLH_START_DATE_FIELD_KEY', 'field_6a159135fec38');
}

if (!defined('AMNESTY_CLH_FINAL_SIGNATURES_MAX')) {
    define('AMNESTY_CLH_FINAL_SIGNATURES_MAX', 99_999_999);
}

if (!defined('AMNESTY_CLH_FIRST_CAMPAIGN_YEAR')) {
    define('AMNESTY_CLH_FIRST_CAMPAIGN_YEAR', 2001);
}

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

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
                'key' => AMNESTY_CLH_START_DATE_FIELD_KEY,
                'label' => 'Date de début',
                'name' => 'start_date_highligth_clh',
                'aria-label' => '',
                'type' => 'date_time_picker',
                'instructions' => '',
                'required' => 1,
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
                'display_format' => 'Y-m-d H:i:s',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 1,
                'default_to_current_date' => 0,
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_6a2f1c8e5d4b7',
                'label' => 'Total final des signatures',
                'name' => 'final_signatures_clh',
                'aria-label' => '',
                'type' => 'number',
                'instructions' => 'Affiché dans le bandeau « Campagne terminée » une fois le mode Temps fort coupé. Laissé vide, la page additionne les compteurs des pétitions.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
                'default_value' => '',
                'min' => 0,
                'max' => '',
                'step' => 1,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_6a16f00bb9903',
                'label' => 'Message à partager',
                'name' => 'message_clh',
                'aria-label' => '',
                'type' => 'textarea',
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
                'instructions' => 'Objet de l\'email de partage.',
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
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'rows' => '',
                'placeholder' => '',
                'new_lines' => '',
            ],
            [
                'key' => 'field_6a4d2c8e1b7f1',
                'label' => 'Nombre de signatures final',
                'name' => 'final_signatures_clh',
                'aria-label' => '',
                // A text input, not a number one: browsers send an empty value for "1 000" in a number
                // input, so the server-side check could not reject it.
                'type' => 'text',
                'instructions' => 'Affiché quand la campagne est terminée. Chiffres uniquement, sans espace (ex. : 1250000). L\'indicateur reste masqué tant que le nombre de signatures et l\'année ne sont pas tous deux renseignés.',
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
                'key' => 'field_6a4d2c8e1b7f2',
                'label' => 'Année de la campagne',
                'name' => 'campaign_year_clh',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => 'Affichée quand la campagne est terminée. L\'indicateur reste masqué tant que le nombre de signatures et l\'année ne sont pas tous deux renseignés.',
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
