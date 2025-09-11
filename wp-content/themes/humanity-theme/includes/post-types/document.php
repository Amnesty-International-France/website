<?php

declare(strict_types=1);

/**
 * Register Custom Post-Type: Document
 */
function amnesty_register_document_cpt(): void
{
    register_post_type(
        'document',
        array(
            'labels' => array(
                'name' => 'Documents',
                'singular_name' => 'Document',
                'add_new' => 'Ajouter un Document',
                'add_new_item' => 'Ajouter un nouveau Document',
                'edit_item' => 'Modifier le Document',
                'new_item' => 'Nouveau Document',
                'view_item' => 'Voir le Document',
                'search_items' => 'Rechercher un Document',
                'not_found' => 'Aucun Document trouvé',
                'not_found_in_trash' => 'Aucun Document dans la corbeille',
            ),
            'public' => false,
            'has_archive' => true,
            'rewrite' => array('slug' => 'documents'),
            'supports' => array('title', 'thumbnail', 'custom-fields', 'excerpt'),
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

add_action('init', 'amnesty_register_document_cpt');

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_688c7477a4701',
        'title' => 'Document',
        'fields' => array(
            array(
                'key' => 'field_688c7478cfe59',
                'label' => 'Upload du document',
                'name' => 'upload_du_document',
                'aria-label' => '',
                'type' => 'file',
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
                'min_size' => '',
                'max_size' => '',
                'mime_types' => '',
                'allow_in_bindings' => 0,
            ),
            array(
                'key' => 'field_689a05696c83f',
                'label' => 'Type libre',
                'name' => 'type_libre',
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
                'key' => 'field_689a05696c84f',
                'label' => 'AI Index',
                'name' => 'ai_index',
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
                'key' => 'field_27dmxqoc8t',
                'label' => 'Document privé',
                'name' => 'document_private',
                'type' => 'true_false',
                'ui' => 1,
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
            ),
            array(
                'key' => 'field_49dhzhpoyv',
                'label' => 'Vie démocratique',
                'name' => 'vie_democratique',
                'type' => 'select',
                'choices' => [
                    'assemblee_generale' => 'Assemblée générale',
                    'conseil_administration' => "Conseil d'administration",
                    'comite_des_candidatures' => 'Comité des candidatures',
                    'conseil_des_finances_et_des_risques_financiers' => 'Conseil des finances et des risques financiers',
                    'representant_des_jeunes' => 'Représentants des jeunes',
                    'conseil_national' => 'Conseil national'
                ],
                'ui' => 1,
                'required' => 0,
                'allow_null' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_27dmxqoc8t',
                            'operator' => '==',
                            'value' => '1',
                        )
                    )
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
            ),
            array(
                'key' => 'field_gdomgbgri6',
                'label' => 'Vie militante',
                'name' => 'vie_militante',
                'type' => 'select',
                'choices' => [
                    'vm1' => 'VM1',
                    'vm2' => 'VM2',
                    'vm3' => 'VM3',
                ],
                'ui' => 1,
                'required' => 0,
                'allow_null' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_27dmxqoc8t',
                            'operator' => '==',
                            'value' => '1',
                        )
                    )
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'document',
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
    ));
});

add_filter('manage_document_posts_columns', function ($columns) {
    $columns['document_private'] = 'Document privé';

    return $columns;
});

add_action('manage_document_posts_custom_column', function ($column, $post_id) {
    if ($column === 'document_private') {
        echo get_field('field_27dmxqoc8t', $post_id) ? 'Oui' : 'Non';
    }
}, 10, 2);

add_action('restrict_manage_posts', function ($post_type) {
    if ($post_type !== 'document') {
        return;
    }

    $selected = $_GET['document_private'] ?? '';

    ?>
	<select name="document_private">
		<option value="">Tous les documents</option>
		<option value="1" <?php selected($selected, '1'); ?>>Privé</option>
		<option value="0" <?php selected($selected, '0'); ?>>Publique</option>
	</select>
	<?php
});

add_action('pre_get_posts', function ($query) {
    global $pagenow;

    if (
        !is_admin() ||
        $pagenow !== 'edit.php' ||
        !$query->is_main_query() ||
        $query->get('post_type') !== 'document'
    ) {
        return;
    }

    $documentPrivateFilter = $_GET['document_private'] ?? '';
    if ($documentPrivateFilter !== '') {
        $query->set('meta_query', [
            [
                'key'   => 'document_private',
                'value' => $documentPrivateFilter,
            ]
        ]);
    }
});

add_action('pre_get_posts', function ($query) {
    if (!is_admin()
        && $query->is_main_query()
        && is_post_type_archive('document')
    ) {
        $meta_query = [
            [
                'key'   => 'document_private',
                'value' => '0',
            ],
        ];

        $query->set('meta_query', $meta_query);
    }
});
