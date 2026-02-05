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
            'labels'              => [
                'name'               => 'Document',
                'singular_name'      => 'Document',
                'add_new'            => 'Ajouter un Document',
                'add_new_item'       => 'Ajouter un nouveau Document',
                'edit_item'          => 'Modifier le Document',
                'new_item'           => 'Nouveau Document',
                'view_item'          => 'Voir le Document',
                'search_items'       => 'Rechercher un Document',
                'not_found'          => 'Aucun Document trouvé',
                'not_found_in_trash' => 'Aucun Document dans la corbeille',
            ],
            'public'              => true,
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'documents' ],
            'supports'            => [ 'title', 'thumbnail', 'custom-fields', 'excerpt' ],
            'menu_icon'           => 'dashicons-media-document',
            'show_in_rest'        => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
        ]
    );
}

add_action('init', 'amnesty_register_document_cpt');

/**
 * Returns whether a document is marked as private.
 */
function amnesty_is_private_document(int $post_id): bool
{
    return '1' === (string) get_post_meta($post_id, 'document_private', true);
}

/**
 * Returns the login URL used for restricted document access.
 */
function amnesty_get_document_login_url(): string
{
    $login_page = get_page_by_path('connectez-vous');
    if ($login_page instanceof WP_Post) {
        $permalink = get_permalink($login_page);
        if (is_string($permalink) && $permalink !== '') {
            return $permalink;
        }
    }

    return home_url('/connectez-vous/');
}

/**
 * Returns the attachment ID configured on a document.
 */
function amnesty_get_document_attachment_id(int $post_id): int
{
    return (int) get_post_meta($post_id, 'upload_du_document', true);
}

/**
 * Returns the attachment URL configured on a document.
 */
function amnesty_get_document_attachment_url(int $post_id): string
{
    $attachment_id = amnesty_get_document_attachment_id($post_id);
    if (! $attachment_id) {
        return '';
    }

    $attachment_url = wp_get_attachment_url($attachment_id);

    return is_string($attachment_url) ? $attachment_url : '';
}

/**
 * Returns the URL to use for document links.
 * Private documents always point to their single URL for auth checks.
 */
function amnesty_get_document_access_url(int $post_id): string
{
    if (amnesty_is_private_document($post_id)) {
        $permalink = get_permalink($post_id);

        return is_string($permalink) ? $permalink : '';
    }

    return amnesty_get_document_attachment_url($post_id);
}

/**
 * Returns the filesystem directory for private document storage.
 */
function amnesty_get_private_document_storage_dir(): string
{
    $default_dir = wp_normalize_path(dirname(ABSPATH) . '/aif-private-documents');
    $storage_dir = apply_filters('amnesty_private_document_storage_dir', $default_dir);

    if (! is_string($storage_dir) || $storage_dir === '') {
        return $default_dir;
    }

    return wp_normalize_path(rtrim($storage_dir, '/\\'));
}

/**
 * Returns the private file path stored for a document.
 */
function amnesty_get_private_document_path(int $post_id): string
{
    $path = get_post_meta($post_id, '_amnesty_private_document_path', true);
    if (! is_string($path) || $path === '') {
        return '';
    }

    $path = wp_normalize_path($path);
    $storage_dir = trailingslashit(amnesty_get_private_document_storage_dir());
    if (! str_starts_with($path, $storage_dir)) {
        return '';
    }

    return $path;
}

/**
 * Moves a file on disk with copy+delete fallback if rename fails.
 */
function amnesty_move_file(string $source_path, string $target_path): bool
{
    if ($source_path === $target_path) {
        return true;
    }

    $target_dir = dirname($target_path);
    if (! is_dir($target_dir) && ! wp_mkdir_p($target_dir)) {
        return false;
    }

    if (@rename($source_path, $target_path)) {
        return true;
    }

    if (! @copy($source_path, $target_path)) {
        return false;
    }

    return @unlink($source_path);
}

/**
 * Clears private storage metadata for a document.
 */
function amnesty_cleanup_private_document_meta(int $post_id): void
{
    delete_post_meta($post_id, '_amnesty_private_document_path');
    delete_post_meta($post_id, '_amnesty_private_document_original_path');
    delete_post_meta($post_id, '_amnesty_private_document_attachment_id');
}

/**
 * Moves/restores the document file according to the private toggle.
 */
function amnesty_sync_document_private_storage(int $post_id): void
{
    if (get_post_type($post_id) !== 'document') {
        return;
    }

    $attachment_id = amnesty_get_document_attachment_id($post_id);
    $private_path = amnesty_get_private_document_path($post_id);
    $stored_attachment_id = (int) get_post_meta($post_id, '_amnesty_private_document_attachment_id', true);

    if (! amnesty_is_private_document($post_id)) {
        if ($private_path !== '' && is_file($private_path) && is_readable($private_path)) {
            $restore_path = get_post_meta($post_id, '_amnesty_private_document_original_path', true);
            if (! is_string($restore_path) || $restore_path === '') {
                $restore_path = (string) get_attached_file($attachment_id);
            }

            if ($restore_path !== '') {
                $restore_path = wp_normalize_path($restore_path);
                $restore_dir = dirname($restore_path);

                if (is_dir($restore_dir) || wp_mkdir_p($restore_dir)) {
                    if (is_file($restore_path)) {
                        $restore_filename = wp_unique_filename($restore_dir, wp_basename($restore_path));
                        $restore_path = wp_normalize_path($restore_dir . '/' . $restore_filename);
                    }

                    if (amnesty_move_file($private_path, $restore_path) && $attachment_id) {
                        $uploads = wp_get_upload_dir();
                        $upload_base_dir = trailingslashit(wp_normalize_path($uploads['basedir']));

                        if (str_starts_with($restore_path, $upload_base_dir)) {
                            $relative_path = ltrim(substr($restore_path, strlen($upload_base_dir)), '/');
                            update_post_meta($attachment_id, '_wp_attached_file', $relative_path);
                        }
                    }
                }
            }
        }

        amnesty_cleanup_private_document_meta($post_id);

        return;
    }

    if (! $attachment_id) {
        return;
    }

    if ($private_path !== '' && $stored_attachment_id === $attachment_id && is_file($private_path)) {
        return;
    }

    if ($private_path !== '' && $stored_attachment_id !== $attachment_id && is_file($private_path)) {
        @unlink($private_path);
    }

    $source_path = (string) get_attached_file($attachment_id);
    $source_path = wp_normalize_path($source_path);

    if ($source_path === '' || ! is_file($source_path) || ! is_readable($source_path)) {
        return;
    }

    $target_dir = wp_normalize_path(amnesty_get_private_document_storage_dir() . '/' . gmdate('Y/m'));
    if (! is_dir($target_dir) && ! wp_mkdir_p($target_dir)) {
        return;
    }

    $target_filename = wp_unique_filename($target_dir, sprintf('%d-%s', $post_id, wp_basename($source_path)));
    $target_path = wp_normalize_path($target_dir . '/' . $target_filename);

    if (! amnesty_move_file($source_path, $target_path)) {
        return;
    }

    update_post_meta($post_id, '_amnesty_private_document_path', $target_path);
    update_post_meta($post_id, '_amnesty_private_document_original_path', $source_path);
    update_post_meta($post_id, '_amnesty_private_document_attachment_id', $attachment_id);
}

/**
 * Streams a file to the response.
 */
function amnesty_stream_file_response(string $file_path, bool $is_private): bool
{
    $file_path = wp_normalize_path($file_path);
    if (! is_file($file_path) || ! is_readable($file_path)) {
        return false;
    }

    $mime_type = wp_check_filetype($file_path)['type'] ?: 'application/octet-stream';
    $file_name = wp_basename($file_path);
    $file_size = filesize($file_path);

    if ($is_private) {
        nocache_headers();
    } else {
        header('Cache-Control: public, max-age=86400');
    }

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    if (is_int($file_size) && $file_size > 0) {
        header('Content-Length: ' . (string) $file_size);
    }

    return false !== readfile($file_path);
}

/**
 * Streams a private document file if available.
 */
function amnesty_stream_private_document(int $post_id): bool
{
    $private_path = amnesty_get_private_document_path($post_id);

    if ($private_path === '' || ! is_file($private_path)) {
        amnesty_sync_document_private_storage($post_id);
        $private_path = amnesty_get_private_document_path($post_id);
    }

    if ($private_path === '') {
        return false;
    }

    return amnesty_stream_file_response($private_path, true);
}

add_action(
    'acf/save_post',
    function ($post_id): void {
        if (! is_numeric($post_id)) {
            return;
        }

        amnesty_sync_document_private_storage((int) $post_id);
    },
    20
);

add_action(
    'before_delete_post',
    function (int $post_id): void {
        if (get_post_type($post_id) !== 'document') {
            return;
        }

        $private_path = amnesty_get_private_document_path($post_id);
        if ($private_path !== '' && is_file($private_path)) {
            @unlink($private_path);
        }

        amnesty_cleanup_private_document_meta($post_id);
    }
);

add_action(
    'acf/include_fields',
    function () {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(
            [
                'key'                   => 'group_688c7477a4701',
                'title'                 => 'Document',
                'fields'                => [
                    [
                        'key'               => 'field_688c7478cfe59',
                        'label'             => 'Upload du document',
                        'name'              => 'upload_du_document',
                        'aria-label'        => '',
                        'type'              => 'file',
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
                        'min_size'          => '',
                        'max_size'          => '',
                        'mime_types'        => '',
                        'allow_in_bindings' => 0,
                    ],
                    [
                        'key'               => 'field_689a05696c83f',
                        'label'             => 'Type libre',
                        'name'              => 'type_libre',
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
                        'key'               => 'field_689a05696c84f',
                        'label'             => 'AI Index',
                        'name'              => 'ai_index',
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
                        'key'               => 'field_27dmxqoc8t',
                        'label'             => 'Document privé',
                        'name'              => 'document_private',
                        'type'              => 'true_false',
                        'ui'                => 1,
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'document',
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
    }
);

add_filter(
    'manage_document_posts_columns',
    function ($columns) {
        $columns['document_private'] = 'Document privé';

        return $columns;
    }
);

add_action(
    'manage_document_posts_custom_column',
    function ($column, $post_id) {
        if ($column === 'document_private') {
            echo get_field('field_27dmxqoc8t', $post_id) ? 'Oui' : 'Non';
        }
    },
    10,
    2
);

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
    add_action(
        'pre_get_posts',
        function ($query) {
            global $pagenow;

            if (
                $pagenow !== 'edit.php' ||
                ! $query->is_main_query() ||
                $query->get('post_type') !== 'document'
            ) {
                return;
            }

            $documentPrivateFilter = $_GET['document_private'] ?? '';
            if ($documentPrivateFilter !== '') {
                $query->set(
                    'meta_query',
                    [
                        [
                            'key'   => 'document_private',
                            'value' => $documentPrivateFilter,
                        ],
                    ]
                );
            }
        }
    );
}

if (! is_admin()) {
    add_action(
        'pre_get_posts',
        function ($query) {
            if ($query->is_main_query() && is_post_type_archive('document')) {
                $meta_query = [
                    [
                        'key'   => 'document_private',
                        'value' => '0',
                    ],
                ];

                $query->set('meta_query', $meta_query);
            }
        }
    );
}

add_filter(
    'posts_search',
    function ($search, $wp_query) {
        global $wpdb;

        if (! empty($wp_query->query_vars['s']) && $wp_query->get('post_type') === 'document') {
            $search = $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($wp_query->query_vars['s']) . '%');
        }

        return $search;
    },
    10,
    2
);
