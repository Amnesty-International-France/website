<?php

/**
 * Register Custom Post Type: EDH
 */

declare(strict_types=1);

function amnesty_register_edh_cpt(): void
{
    register_post_type(
        'edh',
        [
            'labels'              => [
                'name'               => 'EDH',
                'singular_name'      => 'EDH',
                'add_new'            => 'Ajouter un EDH',
                'add_new_item'       => 'Ajouter un nouveau EDH',
                'edit_item'          => 'Modifier un EDH',
                'new_item'           => 'Nouveau EDH',
                'view_item'          => 'Voir le EDH',
                'search_items'       => 'Rechercher un EDH',
                'not_found'          => 'Aucun EDH trouvé',
                'not_found_in_trash' => 'Aucun EDH dans la corbeille',
            ],
            'public'              => true,
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'agir-avec-nous/eduquer-droits-humains/ressources-pedagogiques' ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'menu_icon'           => 'dashicons-groups',
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
        ]
    );
}

add_action('init', 'amnesty_register_edh_cpt');


add_filter('wpseo_breadcrumb_links', 'amnesty_custom_edh_breadcrumbs');

function amnesty_custom_edh_breadcrumbs($links)
{
    if (is_singular('edh') || is_post_type_archive('edh')) {

        $new_parents = [
            [
                'url'  => home_url('/agir-avec-nous/'),
                'text' => 'Agir avec nous',
            ],
            [
                'url'  => home_url('/agir-avec-nous/eduquer-droits-humains/'),
                'text' => 'Éduquer aux droits humains',
            ],
        ];

        array_splice($links, 1, 0, $new_parents);
    }

    return $links;
}

function added_edh_submenus()
{
    $parent_slug = 'edit.php?post_type=edh';

    if (! post_type_exists('edh')) {
        return;
    }

    add_submenu_page(
        $parent_slug,
        'Ajouter un document',
        'Ajouter un document',
        'publish_posts',
        'post-new.php?post_type=edh&support_edh=document'
    );

    add_submenu_page(
        $parent_slug,
        'Ajouter une page',
        'Ajouter une page',
        'publish_posts',
        'post-new.php?post_type=edh&support_edh=page'
    );
}

add_action('admin_menu', 'added_edh_submenus');

function remove_link_add_global_edh()
{
    $parent_slug = 'edit.php?post_type=edh';

    $submenu_slug = 'post-new.php?post_type=edh';

    remove_submenu_page($parent_slug, $submenu_slug);
}

add_action('admin_menu', 'remove_link_add_global_edh', 999);

function edh_parent_file($parent_file)
{
    global $submenu_file, $pagenow;

    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'edh') {

        if (isset($_GET['support_edh']) && $_GET['support_edh'] === 'document') {
            $submenu_file = 'post-new.php?post_type=edh&support_edh=document';
            $parent_file  = 'edit.php?post_type=edh';
        }

        if (isset($_GET['support_edh']) && $_GET['support_edh'] === 'page') {
            $submenu_file = 'post-new.php?post_type=edh&support_edh=page';
            $parent_file  = 'edit.php?post_type=edh';
        }
    }

    return $parent_file;
}

add_filter('parent_file', 'edh_parent_file');

function use_block_editor($use_block_editor, $post_type)
{
    if ('edh' === $post_type) {
        if (isset($_GET['support_edh']) && sanitize_text_field($_GET['support_edh']) === 'document') {
            return false;
        }

        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if ($post_id && get_post_type($post_id) === 'edh') {
            $support = get_field('support_edh', $post_id);
            if ($support === 'Document') {
                return false;
            }
        }
    }
    return $use_block_editor;
}

add_filter('use_block_editor_for_post_type', 'use_block_editor', 10, 2);

function edh_filters($query)
{
    if (! $query->is_main_query() || ! is_post_type_archive('edh')) {
        return;
    }

    $meta_query       = [ 'relation' => 'AND' ];
    $conditions_added = false;

    if (isset($_GET['qtype_de_contenu']) && ! empty($_GET['qtype_de_contenu'])) {
        $content_type     = explode(',', sanitize_text_field($_GET['qtype_de_contenu']));
        $meta_query[]     = [
            'key'     => 'type_de_contenu',
            'value'   => $content_type,
            'compare' => 'IN',
        ];
        $conditions_added = true;
    }

    if (isset($_GET['qtheme']) && ! empty($_GET['qtheme'])) {
        $requirements     = explode(',', sanitize_text_field($_GET['qtheme']));
        $meta_query[]     = [
            'key'     => 'theme',
            'value'   => $requirements,
            'compare' => 'IN',
        ];
        $conditions_added = true;
    }

    if (isset($_GET['qrequirements']) && ! empty($_GET['qrequirements'])) {
        $requirements     = explode(',', sanitize_text_field($_GET['qrequirements']));
        $meta_query[]     = [
            'key'     => 'requirements',
            'value'   => $requirements,
            'compare' => 'IN',
        ];
        $conditions_added = true;
    }

    if (isset($_GET['qactivity_duration']) && ! empty($_GET['qactivity_duration'])) {
        $activity_duration = explode(',', sanitize_text_field($_GET['qactivity_duration']));
        $meta_query[]      = [
            'key'     => 'activity_duration',
            'value'   => $activity_duration,
            'compare' => 'IN',
        ];
        $conditions_added  = true;
    }

    if ($conditions_added) {
        $query->set('meta_query', $meta_query);
    }
}
if (!is_admin()) {
    add_action('pre_get_posts', 'edh_filters');
}

function support_edh_default_value($value, $post_id, $field)
{
    if (! empty($value)) {
        return $value;
    }

    if (isset($_GET['support_edh'])) {
        $param = sanitize_text_field($_GET['support_edh']);

        $param = ucfirst(strtolower($param));

        if (in_array($param, [ 'Document', 'Page' ], true)) {
            return $param;
        }
    }

    return $field;
}

add_filter('acf/load_value/name=support_edh', 'support_edh_default_value', 10, 3);

function edh_acf_fields()
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(
        [
            'key'                   => 'group_68c2a117b68c8',
            'title'                 => 'Paramètre EDH',
            'fields'                => [
                [
                    'key'               => 'field_68c2a118897d7',
                    'label'             => 'Support EDH',
                    'name'              => 'support_edh',
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
                        'Document' => 'Document',
                        'Page'     => 'Page',
                    ],
                    'default_value'     => false,
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
                    'key'               => 'field_68c2a14b84414',
                    'label'             => 'Type de contenu',
                    'name'              => 'type_de_contenu',
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
                        'quiz'                 => 'Quiz',
                        'livret_pedagogique'   => 'Livret pédagogique',
                        'sequence_pedagogique' => 'Séquence pédagogique',
                        'activite_pedagogique' => 'Activité pédagogique',
                        'fiche_de_lecture'     => 'Fiche de lecture',
                    ],
                    'default_value'     => false,
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
                    'key'               => 'field_68c2a1a484415',
                    'label'             => 'Théme',
                    'name'              => 'theme',
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
                        'droits_textes_fondamentaux' => 'Droits et textes fondamentaux',
                        'liberte_expression_droit_manifester' => 'Liberté d\'expression et droit de manifester',
                        'lutte_discriminations'      => 'Lutte contre les discriminations',
                        'lutte_discours_toxiques'    => 'Lutte contre les discours toxiques',
                        'abolition_peine_mort'       => 'Abolition de la peine de mort',
                        'droits_enfant'              => 'Droits de l\'enfant',
                        'droits_femmes'              => 'Droits des femmes',
                        'droits_personnes_refugiees_migrantes' => 'Droits des personnes réfugiées et migrantes',
                        'droits_personnes_LGBTI+'    => 'Droits des personnes LGBTI+',
                        'xlimat_droits_humains'      => 'Climat et droits humains',
                        'conflits_armes_droits_humains' => 'Conflits armés et droits humains',
                        'technologies_droits_humains' => 'Technologies et droits humains',
                        'autre' => 'Autre',
                    ],
                    'default_value'     => 'autre',
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
                    'key'               => 'field_68c2a1f984416',
                    'label'             => 'Besoins',
                    'name'              => 'requirements',
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
                        'se_familiariser' => 'Se familiariser',
                        'approfondir'     => 'Approfondir',
                        'a_voir_lire'     => 'À voir, à lire',
                        'aller_plus_loin' => 'Aller plus loin',
                    ],
                    'default_value'     => false,
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
                    'key'               => 'field_68c2a22184417',
                    'label'             => 'Durée de l\'activité',
                    'name'              => 'activity_duration',
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
                        'moins_une_heure' => 'Moins d\'une heure',
                        'plus_une_heure'  => 'Plus d\'une heure',
                    ],
                    'default_value'     => false,
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
                    'key'               => 'field_68c2a2e910a6d',
                    'label'             => 'Document privé',
                    'name'              => 'document_private',
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
                    'ui_on_text'        => '',
                    'ui_off_text'       => '',
                    'ui'                => 1,
                ],
                [
                    'key'               => 'field_68c2a2b410a6a',
                    'label'             => 'Upload du document',
                    'name'              => 'upload_du_document',
                    'aria-label'        => '',
                    'type'              => 'file',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
                            ],
                        ],
                    ],
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'return_format'     => 'array',
                    'library'           => 'all',
                    'min_size'          => '',
                    'max_size'          => '',
                    'mime_types'        => '',
                    'allow_in_bindings' => 0,
                ],
                [
                    'key'               => 'field_68c2a2d810a6b',
                    'label'             => 'Type libre',
                    'name'              => 'type_libre',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
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
                    'key'               => 'field_68c2a2e010a6c',
                    'label'             => 'AI Index',
                    'name'              => 'ai_index',
                    'aria-label'        => '',
                    'type'              => 'text',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
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
                    'key'               => 'field_68c2a2e910a6d',
                    'label'             => 'Document privé',
                    'name'              => 'document_private',
                    'aria-label'        => '',
                    'type'              => 'true_false',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
                            ],
                        ],
                    ],
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'message'           => '',
                    'default_value'     => 0,
                    'allow_in_bindings' => 0,
                    'ui'                => 1,
                    'ui_on_text'        => '',
                    'ui_off_text'       => '',
                ],
                [
                    'key'               => 'field_68c2a30710a6e',
                    'label'             => 'Vie démocratique',
                    'name'              => 'vie_democratique',
                    'aria-label'        => '',
                    'type'              => 'select',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a2e910a6d',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
                            ],
                        ],
                    ],
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'choices'           => [
                        'assemblee_generale'     => 'Assemblée génerale',
                        'conseil_administration' => 'Conseil d\'administration',
                        'comite_candidatures'    => 'Comité des candidatures',
                        'conseil_finances_risques_financiers' => 'Conseil des finances et des risques financiers',
                        'representants_jeunes'   => 'Représentants des jeunes',
                        'conseil_national'       => 'Conseil national',
                    ],
                    'default_value'     => false,
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
                    'key'               => 'field_68c2a34910a6f',
                    'label'             => 'Vie militante',
                    'name'              => 'vie_militante',
                    'aria-label'        => '',
                    'type'              => 'select',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field'    => 'field_68c2a2e910a6d',
                                'operator' => '==',
                                'value'    => '1',
                            ],
                            [
                                'field'    => 'field_68c2a118897d7',
                                'operator' => '==',
                                'value'    => 'Document',
                            ],
                        ],
                    ],
                    'wrapper'           => [
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ],
                    'choices'           => [
                        'VM1' => 'VM1',
                        'VM2' => 'VM2',
                        'VM3' => 'VM3',
                    ],
                    'default_value'     => false,
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
            ],
            'location'              => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'edh',
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

add_action('acf/include_fields', 'edh_acf_fields');

function manage_edh_posts_columns($columns)
{
    $new = [];
    foreach ($columns as $key => $value) {
        $new[ $key ] = $value;
        if ('title' === $key) {
            $new['support_edh'] = __('Support EDH', 'textdomain');
        }
    }
    return $new;
}

add_filter('manage_edh_posts_columns', 'manage_edh_posts_columns');

add_action(
    'manage_edh_posts_custom_column',
    function ($column, $post_id) {
        if ('support_edh' === $column) {
            $value = get_field('support_edh', $post_id);
            echo $value ? esc_html($value) : '—';
        }
    },
    10,
    2
);

add_filter(
    'manage_edit-edh_sortable_columns',
    function ($columns) {
        $columns['support_edh'] = 'support_edh';
        return $columns;
    }
);

function pre_get_edh_posts($query)
{
    if (! $query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'support_edh') {
        $query->set('meta_key', 'support_edh');
        $query->set('orderby', 'meta_value');
    }
}
if (is_admin()) {
    add_action('pre_get_posts', 'pre_get_edh_posts');
}

function target_link_card_edh($block_content, $block)
{
    if ('amnesty-core/edh-card' === $block['blockName']) {
        $post_id = get_the_ID();
        $support = get_field('support_edh', $post_id);

        if ('Document' === $support) {
            $doc = get_field('upload_du_document', $post_id);
            if ($doc && isset($doc['url'])) {
                $block_content = str_replace(
                    'href="' . get_permalink($post_id) . '"',
                    'href="' . esc_url($doc['url']) . '" target="_blank"',
                    $block_content
                );
            }
        }
    }

    return $block_content;
}

add_filter('render_block', 'target_link_card_edh', 10, 2);

function disabled_support_edh_field($hook)
{
    global $post_type;

    if ('edh' !== $post_type) {
        return;
    }

    wp_add_inline_script(
        'jquery-core',
        "
        jQuery(document).ready(function($) {
            const field = document.querySelector(\"[name='acf[field_68c2a118897d7]']\");
            if (field) {
                field.disabled = true;

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = field.name;
                hidden.value = field.value;
                field.parentNode.appendChild(hidden);
            }
        });
        "
    );
}

add_action('admin_enqueue_scripts', 'disabled_support_edh_field');
