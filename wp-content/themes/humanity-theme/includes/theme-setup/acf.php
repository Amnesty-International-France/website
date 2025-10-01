<?php

add_action(
    'acf/include_fields',
    function () {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(
            [
                'key'                   => 'group_682487476f0cb',
                'title'                 => 'Catégorie éditoriale',
                'fields'                => [
                    [
                        'key'               => 'field_68248747c71a5',
                        'label'             => 'Catégorie éditoriale',
                        'name'              => 'editorial_category',
                        'aria-label'        => '',
                        'type'              => 'select',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'choices'           => [
                            'enquetes'    => 'Enquêtes',
                            'entretiens'  => 'Entretiens',
                            'portraits'   => 'Portraits',
                            'rapports'    => 'Rapports',
                            'temoignages' => 'Témoignages',
                            'tribunes'    => 'Tribunes',
                        ],
                        'default_value'     => false,
                        'return_format'     => 'array',
                        'multiple'          => 0,
                        'allow_null'        => 1,
                        'allow_in_bindings' => 0,
                        'ui'                => 0,
                        'ajax'              => 0,
                        'placeholder'       => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'post',
                        ],
                        [
                            'param'    => 'post_category',
                            'operator' => '==',
                            'value'    => 'category:actualites',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'side',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_6823683e775d5',
                'title'                 => 'Hero archive page',
                'fields'                => [
                    [
                        'key'               => 'field_6823683e95956',
                        'label'             => 'category_image',
                        'name'              => 'category_image',
                        'aria-label'        => '',
                        'type'              => 'image',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'return_format'     => 'array',
                        'library'           => 'all',
                        'min_width'         => '',
                        'min_height'        => '',
                        'min_size'          => '',
                        'max_width'         => '',
                        'max_height'        => '',
                        'max_size'          => '',
                        'mime_types'        => '',
                        'allow_in_bindings' => 0,
                        'preview_size'      => 'medium',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'taxonomy',
                            'operator' => '==',
                            'value'    => 'category',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_680b40638d861',
                'title'                 => 'Prismic Import',
                'fields'                => [
                    [
                        'key'               => 'field_680b40633bb1e',
                        'label'             => 'prismic_json',
                        'name'              => 'prismic_json',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'post',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'page',
                        ],
                        [
                            'param'    => 'page_template',
                            'operator' => '!=',
                            'value'    => 'page-the-chronicle-promo',
                        ],
                        [
                            'param'    => 'page_template',
                            'operator' => '!=',
                            'value'    => 'archive-chronique',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'tribe_events',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'fiche_pays',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'landmark',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'local-structures',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'petition',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_6824a398670a2',
                'title'                 => 'Sommaire',
                'fields'                => [
                    [
                        'key'               => 'field_6824a39804731',
                        'label'             => 'Afficher le sommaire',
                        'name'              => 'display_toc',
                        'aria-label'        => '',
                        'type'              => 'true_false',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'message'           => '',
                        'default_value'     => 0,
                        'allow_in_bindings' => 0,
                        'ui'                => 0,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_taxonomy',
                            'operator' => '==',
                            'value'    => 'category:dossiers',
                        ],
                    ],
                ],
                'menu_order'            => -1,
                'position'              => 'side',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_682c408ccdea5',
                'title'                 => 'Nom de catégorie au singulier',
                'fields'                => [
                    [
                        'key'               => 'field_682c408d2e95b',
                        'label'             => 'Nom de catégorie au singulier',
                        'name'              => 'category_singular_name',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'taxonomy',
                            'operator' => '==',
                            'value'    => 'category',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 1,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_685a9d583a748',
                'title'                 => 'Articles associés',
                'fields'                => [
                    [
                        'key'                  => 'field_685a9d59c62d3',
                        'label'                => 'Articles associés',
                        'name'                 => '_related_posts_selected',
                        'aria-label'           => '',
                        'type'                 => 'relationship',
                        'instructions'         => '',
                        'required'             => 0,
                        'conditional_logic'    => 0,
                        'wrapper'              => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'post_type'            => [
                            0 => 'post',
                            1 => 'landmark',
                        ],
                        'post_status'          => [
                            0 => 'publish',
                        ],
                        'taxonomy'             => '',
                        'filters'              => [
                            0 => 'search',
                            1 => 'post_type',
                        ],
                        'return_format'        => 'object',
                        'min'                  => '',
                        'max'                  => 3,
                        'allow_in_bindings'    => 0,
                        'elements'             => '',
                        'bidirectional'        => 0,
                        'bidirectional_target' => [],
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_category',
                            'operator' => '==',
                            'value'    => 'category:actualites',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_category',
                            'operator' => '==',
                            'value'    => 'category:chroniques',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_category',
                            'operator' => '==',
                            'value'    => 'category:campagnes',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_category',
                            'operator' => '==',
                            'value'    => 'category:dossiers',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'landmark',
                        ],
                    ],
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'page',
                        ],
                        [
                            'param'    => 'post_type',
                            'operator' => '!=',
                            'value'    => 'chronique',
                        ],
                        [
                            'param'    => 'page_template',
                            'operator' => '!=',
                            'value'    => 'page-the-chronicle-promo',
                        ],
                        [
                            'param'    => 'page_template',
                            'operator' => '!=',
                            'value'    => 'archive-chronique',
                        ],
                        [
                            'param'    => 'page_type',
                            'operator' => '!=',
                            'value'    => 'front_page',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 1,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_686fcf4be56a8',
                'title'                 => 'Lien vers la campagne de soutien',
                'fields'                => [
                    [
                        'key'               => 'field_686fcf4cb8a32',
                        'label'             => 'Lien vers la campagne de soutien',
                        'name'              => 'link-donation',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'page_template',
                            'operator' => '==',
                            'value'    => 'page-fondation',
                        ],
                    ],
                    [
                        [
                            'param'    => 'page_template',
                            'operator' => '==',
                            'value'    => 'page-don',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 1,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_68dcedd4c60f1',
                'title'                 => 'Formation',
                'fields'                => [
                    [
                        'key'               => 'field_6883319051ddd',
                        'label'             => 'Description',
                        'name'              => 'description',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                    [
                        'key'               => 'field_6883319051ddc',
                        'label'             => 'Lieu de formation',
                        'name'              => 'lieu',
                        'aria-label'        => '',
                        'type'              => 'select',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'choices'           => [
                            'secretariat-national' => 'Secrétariat national',
                            'region'               => 'Région',
                            'a-distance'           => 'À distance',
                        ],
                        'default_value'     => 'Secrétariat national',
                        'return_format'     => 'value',
                        'multiple'          => 0,
                        'allow_null'        => 0,
                        'allow_in_bindings' => 0,
                        'ui'                => 0,
                        'ajax'              => 0,
                        'placeholder'       => '',
                        'create_options'    => 0,
                        'save_options'      => 0,
                    ],
                    [
                        'key'               => 'field_688334da51ddd',
                        'label'             => 'Date de la formation',
                        'name'              => 'date',
                        'aria-label'        => '',
                        'type'              => 'date_picker',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'display_format'    => 'd/m/Y',
                        'return_format'     => 'd/m/Y',
                        'first_day'         => 1,
                        'allow_in_bindings' => 0,
                    ],
                    [
                        'key'               => 'field_68dceddb24370',
                        'label'             => 'Date de fin de la formation',
                        'name'              => 'date_fin',
                        'aria-label'        => '',
                        'type'              => 'date_picker',
                        'instructions'      => 'Laisser ce champ vide si la formation ne dure qu\'une journée',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'display_format'    => 'd/m/Y',
                        'return_format'     => 'd/m/Y',
                        'first_day'         => 1,
                        'allow_in_bindings' => 0,
                    ],
                    [
                        'key'               => 'field_68833cec4b6fa',
                        'label'             => 'Réservé aux membres',
                        'name'              => 'members_only',
                        'aria-label'        => '',
                        'type'              => 'true_false',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'message'           => 'Réservé aux membres',
                        'default_value'     => 0,
                        'allow_in_bindings' => 0,
                        'ui'                => 0,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ],
                    [
                        'key'                       => 'field_688344d2380a3',
                        'label'                     => 'Catégories',
                        'name'                      => 'categories',
                        'aria-label'                => '',
                        'type'                      => 'select',
                        'instructions'              => '',
                        'required'                  => 0,
                        'conditional_logic'         => 0,
                        'wrapper'                   => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'choices'                   => [
                            'base'            => 'La base',
                            'thematics'       => 'Thématiques',
                            'skills'          => 'Compétences',
                            'human-rights-education' => 'Education aux Droits Humains',
                            'local-structures' => 'Structures locales',
                        ],
                        'default_value'             => [
                            0 => 'La base',
                        ],
                        'multiple'                  => 0,
                        'return_format'             => 'value',
                        'allow_custom'              => 0,
                        'allow_in_bindings'         => 0,
                        'layout'                    => 'vertical',
                        'toggle'                    => 0,
                        'save_custom'               => 0,
                        'custom_choice_button_text' => 'Ajouter un nouveau choix',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'training',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'side',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 1,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_6888934b5990e',
                'title'                 => 'sur-titre',
                'fields'                => [
                    [
                        'key'               => 'field_6888934b292bb',
                        'label'             => 'Sur-titre',
                        'name'              => 'sur-titre',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'page_template',
                            'operator' => '==',
                            'value'    => 'page-fondation',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'side',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_68a44f82d59d0',
                'title'                 => 'Import',
                'fields'                => [
                    [
                        'key'               => 'field_68a44f83744ae',
                        'label'             => 'status',
                        'name'              => 'status',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'press-release',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        $personnes_page = get_page_by_path('personnes', OBJECT, 'page');
        $personnes_id   = $personnes_page->ID ?? 0;
        acf_add_local_field_group(
            [
                'key'                   => 'group_68a488d09855d',
                'title'                 => 'Portrait',
                'fields'                => [
                    [
                        'key'               => 'field_68a488d0573ca',
                        'label'             => 'shortTitle',
                        'name'              => 'shorttitle',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                    [
                        'key'               => 'field_68a48938573cb',
                        'label'             => 'enable10jps',
                        'name'              => 'enable10jps',
                        'aria-label'        => '',
                        'type'              => 'true_false',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'message'           => '',
                        'default_value'     => 0,
                        'allow_in_bindings' => 0,
                        'ui'                => 0,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ],
                    [
                        'key'               => 'field_68a48a21573cc',
                        'label'             => 'title10jps',
                        'name'              => 'title10jps',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field'    => 'field_68a48938573cb',
                                    'operator' => '==',
                                    'value'    => '1',
                                ],
                            ],
                        ],
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                    [
                        'key'               => 'field_68a48a78573cd',
                        'label'             => 'resume10jps',
                        'name'              => 'resume10jps',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field'    => 'field_68a48938573cb',
                                    'operator' => '==',
                                    'value'    => '1',
                                ],
                            ],
                        ],
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                    [
                        'key'               => 'field_68a48b25573ce',
                        'label'             => 'image10jps',
                        'name'              => 'image10jps',
                        'aria-label'        => '',
                        'type'              => 'image',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field'    => 'field_68a48938573cb',
                                    'operator' => '==',
                                    'value'    => '1',
                                ],
                            ],
                        ],
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'return_format'     => 'id',
                        'library'           => 'all',
                        'min_width'         => '',
                        'min_height'        => '',
                        'min_size'          => '',
                        'max_width'         => '',
                        'max_height'        => '',
                        'max_size'          => '',
                        'mime_types'        => '',
                        'allow_in_bindings' => 0,
                        'preview_size'      => 'medium',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'page',
                        ],
                        [
                            'param'    => 'page_template',
                            'operator' => '!=',
                            'value'    => 'page-the-chronicle-promo',
                        ],
                        [
                            'param'    => 'page_parent',
                            'operator' => '==',
                            'value'    => (string) $personnes_id,
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group(
            [
                'key'                   => 'group_689d9ee82e987',
                'title'                 => 'Hero large page "La chronique"',
                'fields'                => [
                    [
                        'key'               => 'field_689d9fc4ea0f7',
                        'label'             => 'Texte du bouton de lien',
                        'name'              => 'btn_link_text',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => 'Abonnez-vous pour 3€/mois',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                    [
                        'key'               => 'field_689da0c9ea0f8',
                        'label'             => 'Lien du boutton',
                        'name'              => 'btn_link',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => 'https://soutenir.amnesty.fr/b?cid=365&lang=fr_FR&reserved_originecode=null',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'page_template',
                            'operator' => '==',
                            'value'    => 'page-the-chronicle-promo',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );

        acf_add_local_field_group([
            'key' => 'group_689dadd09f8fa',
            'title' => 'Chapo',
            'fields' => [
                [
                    'key' => 'field_689dadd67591e',
                    'label' => 'Texte du chapo',
                    'name' => 'chapo_text',
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
                    'default_value' => 'La Chronique, c’est LE magazine des droits humains.
Chaque mois, des journalistes enquêtent sur des sujets liés aux droits humains.',
                    'maxlength'         => '',
                    'allow_in_bindings' => 0,
                    'rows'              => '',
                    'placeholder'       => '',
                    'new_lines'         => '',
                ],
            ],
            'location'              => [
                [
                    [
                        'param'    => 'page_template',
                        'operator' => '==',
                        'value'    => 'page-the-chronicle-promo',
                    ],
                ],
            ],
            'menu_order'            => 1,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen'        => '',
            'active'                => true,
            'description'           => '',
            'show_in_rest'          => 0,
        ]);

        acf_add_local_field_group([
            'key' => 'group_689dbd6d52167',
            'title' => 'Mise en exergue',
            'fields' => [
                [
                    'key' => 'field_689dbd72671d7',
                    'label' => 'Titre mise en exergue',
                    'name' => 'promo_callout_title',
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
                    'default_value' => 'Lorem ipsum dolor sit amet',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ],
                [
                    'key' => 'field_689dbdd2671d8',
                    'label' => 'Contenu mise en exergue',
                    'name' => 'promo_callout_text',
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
                    'default_value' => 'Consectetur adipiscing elit. Curabitur nec neque erat. Vestibulum molestie sem augue, ac congue nulla faucibus id. Sed placerat scelerisque tristique.',
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
                        'param' => 'page_template',
                        'operator' => '==',
                        'value' => 'page-the-chronicle-promo',
                    ],
                ],
            ],
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ]);

        acf_add_local_field_group(
            [
                'key'                   => 'group_68c1269a0e840',
                'title'                 => 'Lien popin',
                'fields'                => [
                    [
                        'key'               => 'field_68c1269ac17c3',
                        'label'             => 'Lien vers l\'action de soutien',
                        'name'              => 'link_action_popin',
                        'aria-label'        => '',
                        'type'              => 'url',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                    ],
                    [
                        'key'               => 'field_68c126dfc17c4',
                        'label'             => 'Label du bouton',
                        'name'              => 'button_text_popin',
                        'aria-label'        => '',
                        'type'              => 'text',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'default_value'     => '',
                        'maxlength'         => '',
                        'allow_in_bindings' => 0,
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'pop-in',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );
    }
);

add_action(
    'acf/save_post',
    function ($post_id) {
        if (get_post_type($post_id) !== 'post') {
            return;
        }

        $categories = wp_get_post_categories($post_id, [ 'fields' => 'slugs' ]);

        if (! in_array('actualites', $categories, true)) {
            delete_field('editorial_category', $post_id);
        }
    },
    20
);
