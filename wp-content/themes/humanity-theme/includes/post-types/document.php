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

if (! function_exists('amnesty_document_is_private')) {
    /**
     * Check whether a document is marked as private.
     *
     * @param int $post_id The document post ID.
     *
     * @return bool
     */
    function amnesty_document_is_private(int $post_id): bool
    {
        return (bool) get_post_meta($post_id, 'document_private', true);
    }
}

if (! function_exists('amnesty_document_get_download_url')) {
    /**
     * Return the appropriate download URL for a document.
     *
     * @param int                 $post_id    The document post ID.
     * @param array<string,mixed> $attachment Attachment data from ACF, if available.
     *
     * @return string
     */
    function amnesty_document_get_download_url(int $post_id, array $attachment = []): string
    {
        if (amnesty_document_is_private($post_id)) {
            return get_permalink($post_id) ?: '';
        }

        if (empty($attachment) && function_exists('get_field')) {
            $attachment = (array) get_field('upload_du_document', $post_id);
        }

        if (! empty($attachment['url'])) {
            return $attachment['url'];
        }

        if (! empty($attachment['ID'])) {
            $url = wp_get_attachment_url($attachment['ID']);
            if ($url) {
                return $url;
            }
        }

        return get_permalink($post_id) ?: '';
    }
}

if (! function_exists('amnesty_document_get_private_uploads_dir')) {
    /**
     * Resolve the base directory for private document files.
     *
     * @return string
     */
    function amnesty_document_get_private_uploads_dir(): string
    {
        $uploads = wp_get_upload_dir();
        $base = untrailingslashit(wp_normalize_path($uploads['basedir']));

        return $base . '/private';
    }
}

if (! function_exists('amnesty_document_get_attachment_id')) {
    /**
     * Get the attachment ID for a document.
     *
     * @param int $post_id The document post ID.
     *
     * @return int
     */
    function amnesty_document_get_attachment_id(int $post_id): int
    {
        $attachment_id = (int) get_post_meta($post_id, 'upload_du_document', true);

        if ($attachment_id) {
            return $attachment_id;
        }

        if (function_exists('get_field')) {
            $attachment = get_field('upload_du_document', $post_id);
            if (is_array($attachment) && ! empty($attachment['ID'])) {
                return (int) $attachment['ID'];
            }

            if (is_numeric($attachment)) {
                return (int) $attachment;
            }
        }

        return 0;
    }
}

if (! function_exists('amnesty_document_get_upload_relative_path')) {
    /**
     * Get the relative uploads path stored on an attachment.
     *
     * @param int $attachment_id The attachment ID.
     *
     * @return string
     */
    function amnesty_document_get_upload_relative_path(int $attachment_id): string
    {
        $relative = get_post_meta($attachment_id, '_wp_attached_file', true);

        return is_string($relative) ? ltrim($relative, '/') : '';
    }
}

if (! function_exists('amnesty_document_get_public_file_path')) {
    /**
     * Build the public uploads path for an attachment.
     *
     * @param int $attachment_id The attachment ID.
     *
     * @return string
     */
    function amnesty_document_get_public_file_path(int $attachment_id): string
    {
        $relative = amnesty_document_get_upload_relative_path($attachment_id);
        if ($relative === '') {
            return '';
        }

        $uploads = wp_get_upload_dir();
        if (! empty($uploads['error'])) {
            return '';
        }

        $base = untrailingslashit(wp_normalize_path($uploads['basedir']));

        return $base . '/' . $relative;
    }
}

if (! function_exists('amnesty_document_get_private_file_path')) {
    /**
     * Build the private storage path for an attachment.
     *
     * @param int $attachment_id The attachment ID.
     *
     * @return string
     */
    function amnesty_document_get_private_file_path(int $attachment_id): string
    {
        $relative = amnesty_document_get_upload_relative_path($attachment_id);
        if ($relative === '') {
            return '';
        }

        $base = amnesty_document_get_private_uploads_dir();

        return $base . '/' . $relative;
    }
}

if (! function_exists('amnesty_document_move_attachment_file')) {
    /**
     * Move the attachment file between public uploads and private storage.
     *
     * @param int  $attachment_id The attachment ID.
     * @param bool $to_private    Whether to move to private storage.
     *
     * @return bool
     */
    function amnesty_document_move_attachment_file(int $attachment_id, bool $to_private): bool
    {
        $relative = amnesty_document_get_upload_relative_path($attachment_id);
        if ($relative === '') {
            return false;
        }

        $uploads = wp_get_upload_dir();
        if (! empty($uploads['error'])) {
            return false;
        }

        $public_base = untrailingslashit(wp_normalize_path($uploads['basedir']));
        $private_base = amnesty_document_get_private_uploads_dir();

        $public_path = $public_base . '/' . $relative;
        $private_path = $private_base . '/' . $relative;

        $source = $to_private ? $public_path : $private_path;
        $destination = $to_private ? $private_path : $public_path;

        if (! file_exists($source)) {
            if (file_exists($destination)) {
                if ($to_private) {
                    update_post_meta($attachment_id, '_amnesty_private_file', $relative);
                } else {
                    delete_post_meta($attachment_id, '_amnesty_private_file');
                }

                return true;
            }

            return false;
        }

        if (! wp_mkdir_p(dirname($destination))) {
            return false;
        }

        $copied = @copy($source, $destination);
        if (!$copied || !file_exists($destination) || !(filesize($destination) === filesize($source))) {
            return false;
        }

        $meta = wp_get_attachment_metadata($attachment_id);
        if (is_array($meta) && ! empty($meta['sizes']) && is_array($meta['sizes'])) {
            $relative_dir = dirname($relative);
            $relative_prefix = $relative_dir === '.' ? '' : trailingslashit($relative_dir);

            $movedSources = [];
            $movedDestinations = [];
            $movedCount = 0;
            $expectedCount = 0;

            foreach ($meta['sizes'] as $size) {
                if (empty($size['file'])) {
                    continue;
                }

                $size_relative = $relative_prefix . $size['file'];
                $size_source = ($to_private ? $public_base : $private_base) . '/' . $size_relative;
                $size_destination = ($to_private ? $private_base : $public_base) . '/' . $size_relative;

                if (!file_exists($size_source)) {
                    continue;
                }

                if (!wp_mkdir_p(dirname($size_destination))) {
                    continue;
                }

                $expectedCount++;
                $copied = copy($size_source, $size_destination);
                if (!$copied || !file_exists($size_destination) || !(filesize($size_destination) === filesize($size_source))) {
                    continue;
                }

                $movedCount++;
                $movedSources[] = $size_source;
                $movedDestinations[] = $size_destination;
            }

            if ($movedCount !== $expectedCount) {
                foreach ($movedDestinations as $movedDestination) {
                    @unlink($movedDestination);
                }

                @unlink($destination);
                return false;
            }

            foreach ($movedSources as $movedSource) {
                @unlink($movedSource);
            }
        }

        @unlink($source);
        if ($to_private) {
            update_post_meta($attachment_id, '_amnesty_private_file', $relative);
        } else {
            delete_post_meta($attachment_id, '_amnesty_private_file');
        }

        return true;
    }
}

if (! function_exists('amnesty_document_sync_private_file')) {
    /**
     * Move the document file based on its privacy flag.
     *
     * @param int     $post_id The document ID.
     * @param WP_Post $post    The document post.
     *
     * @return void
     */
    function amnesty_document_sync_private_file(int $post_id, WP_Post $post, bool $update): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_type !== 'document') {
            return;
        }

        $attachment_id = amnesty_document_get_attachment_id($post_id);
        if (! $attachment_id) {
            return;
        }

        $is_private = amnesty_document_is_private($post_id);
        $moved = amnesty_document_move_attachment_file($attachment_id, $is_private);

        if (!$moved) {
            update_field('field_27dmxqoc8t', !$is_private, $post_id);
        }
    }
}

add_action('save_post', 'amnesty_document_sync_private_file', 999, 3);

add_action(
    'acf/save_post',
    function ($post_id) {
        if (! is_numeric($post_id)) {
            return;
        }

        $post_id = (int) $post_id;
        $post = get_post($post_id);
        if (! $post) {
            return;
        }

        amnesty_document_sync_private_file($post_id, $post, true);
    },
    999
);

add_filter(
    'get_attached_file',
    function ($file, $attachment_id) {
        $private_relative = get_post_meta($attachment_id, '_amnesty_private_file', true);
        if (! $private_relative) {
            return $file;
        }

        $private_path = amnesty_document_get_private_file_path((int) $attachment_id);
        if ($private_path === '') {
            return $file;
        }

        return $private_path;
    },
    10,
    2
);

if (! function_exists('amnesty_document_find_by_attachment_id')) {
    /**
     * Find a document post using an attachment ID.
     *
     * @param int $attachment_id The attachment ID.
     *
     * @return int
     */
    function amnesty_document_find_by_attachment_id(int $attachment_id): int
    {
        $documents = get_posts(
            [
                'post_type'      => 'document',
                'post_status'    => 'publish',
                'fields'         => 'ids',
                'posts_per_page' => 1,
                'meta_key'       => 'upload_du_document',
                'meta_value'     => $attachment_id,
            ]
        );

        return $documents ? (int) $documents[0] : 0;
    }
}

if (! function_exists('amnesty_document_maybe_redirect_uploads')) {
    /**
     * Redirect direct uploads access to the document permalink if available.
     *
     * @return void
     */
    function amnesty_document_maybe_redirect_uploads(): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if ($request_uri === '') {
            return;
        }

        $path = wp_parse_url($request_uri, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return;
        }

        $uploads = wp_get_upload_dir();
        if (! empty($uploads['error'])) {
            return;
        }

        $uploads_path = wp_parse_url($uploads['baseurl'], PHP_URL_PATH);
        if (! is_string($uploads_path) || $uploads_path === '') {
            return;
        }

        $uploads_path = untrailingslashit($uploads_path);
        if (strpos($path, $uploads_path . '/') !== 0) {
            return;
        }

        $relative_path = ltrim(substr($path, strlen($uploads_path)), '/');
        $request_url = trailingslashit($uploads['baseurl']) . $relative_path;
        $attachment_id = attachment_url_to_postid($request_url);
        if (! $attachment_id) {
            return;
        }

        $document_id = amnesty_document_find_by_attachment_id($attachment_id);
        if (! $document_id) {
            return;
        }

        if (! function_exists('amnesty_document_is_private') || ! amnesty_document_is_private($document_id)) {
            return;
        }

        wp_redirect(get_permalink($document_id), 301);
        exit;
    }
}

add_action('template_redirect', 'amnesty_document_maybe_redirect_uploads', 1);

if (! function_exists('amnesty_document_maybe_redirect_attachment_page')) {
    /**
     * Redirect attachment pages to their parent document if the document is private.
     * This handles cases where the PDF attachment has its own permalink like:
     * /documents/document-slug/attachment-slug/
     *
     * @return void
     */
    function amnesty_document_maybe_redirect_attachment_page(): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || ! is_attachment()) {
            return;
        }

        $attachment_id = get_the_ID();
        if (! $attachment_id) {
            return;
        }

        $document_id = amnesty_document_find_by_attachment_id($attachment_id);
        if (! $document_id) {
            return;
        }

        if (! function_exists('amnesty_document_is_private') || ! amnesty_document_is_private($document_id)) {
            return;
        }

        wp_redirect(get_permalink($document_id), 301);
        exit;
    }
}

add_action('template_redirect', 'amnesty_document_maybe_redirect_attachment_page', 1);
