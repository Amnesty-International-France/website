<?php

declare(strict_types=1);

/**
 * Register Custom Post-Type: Document
 */
function amnesty_register_document_cpt(): void
{
    register_post_type(
        'document',
        [
            'labels' => [
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
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'documents'],
            'supports' => ['title', 'thumbnail', 'custom-fields', 'excerpt'],
            'menu_icon' => 'dashicons-admin-page',
            'show_in_rest' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 20,
        ]
    );
}

add_action('init', 'amnesty_register_document_cpt');

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_688c7477a4701',
        'title' => 'Document',
        'fields' => [
            [
                'key' => 'field_688c7478cfe59',
                'label' => 'Upload du document',
                'name' => 'upload_du_document',
                'aria-label' => '',
                'type' => 'file',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'array',
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => '',
                'allow_in_bindings' => 0,
            ],
            [
                'key' => 'field_689a05696c83f',
                'label' => 'Type libre',
                'name' => 'type_libre',
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
                'key' => 'field_689a05696c84f',
                'label' => 'AI Index',
                'name' => 'ai_index',
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
                'key' => 'field_27dmxqoc8t',
                'label' => 'Document privé',
                'name' => 'document_private',
                'type' => 'true_false',
                'ui' => 1,
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'document',
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
        'show_in_rest' => 1,
    ]);
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

add_action(
    'restrict_manage_posts',
    function ($post_type) {
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
    }
);

if (is_admin()) {
    add_action('pre_get_posts', function ($query) {
        global $pagenow;

        if (
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
                ],
            ]);
        }
    });
}

if (!is_admin()) {
    add_action('pre_get_posts', function ($query) {
        if ($query->is_main_query() && is_post_type_archive('document')) {
            $meta_query = [
                [
                    'key' => 'document_private',
                    'value' => '0',
                ],
            ];

            $query->set('meta_query', $meta_query);
        }
    });
}

add_filter('posts_search', function ($search, $wp_query) {
    global $wpdb;

    if (!empty($wp_query->query_vars['s']) && $wp_query->get('post_type') === 'document') {
        $search = $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($wp_query->query_vars['s']) . '%');
    }

    return $search;
}, 10, 2);
