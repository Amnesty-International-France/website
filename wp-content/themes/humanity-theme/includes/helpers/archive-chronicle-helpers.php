<?php

/**
 * Helper functions and tweaks related to the chronicle archives page.
 */

declare(strict_types=1);

if (! function_exists('amnesty_custom_chronicle_rewrite_rules')) {
    /**
     * Adds dynamic rewrite rules for chronicles,
     * so that WordPress understands the custom URL structure.
     */
    function amnesty_custom_chronicle_rewrite_rules(): void
    {
        if (!function_exists('amnesty_get_chronicle_structure_info')) {
            return;
        }

        $chronicle_info = amnesty_get_chronicle_structure_info();
        if (!$chronicle_info || empty($chronicle_info['archives_url'])) {
            return;
        }

        $archive_path = trim(wp_parse_url($chronicle_info['archives_url'], PHP_URL_PATH), '/');

        add_rewrite_rule(
            '^' . $archive_path . '/([^/]+)/?$',
            'index.php?post_type=chronique&chronique=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^' . $archive_path . '/?$',
            'index.php?pagename=' . $archive_path,
            'top'
        );
    }
}
add_action('init', 'amnesty_custom_chronicle_rewrite_rules');

if (! function_exists('amnesty_remove_featured_image_for_templates')) {
    function amnesty_remove_featured_image_for_templates()
    {
        $screen = get_current_screen();
        if ($screen === null || ! isset($screen->id) || 'page' !== $screen->id || ! is_admin()) {
            return;
        }

        $post_id = !empty($_GET['post']) ? $_GET['post'] : null;
        $template = '';

        if ($post_id) {
            $template = get_post_meta($post_id, '_wp_page_template', true);
        } elseif (isset($_GET['page_template'])) {
            $template = $_GET['page_template'];
        }

        if ($template === 'archive-chronique') {
            remove_post_type_support('page', 'thumbnail');
        }
    }
}
add_action('load-post.php', 'amnesty_remove_featured_image_for_templates');
add_action('load-post-new.php', 'amnesty_remove_featured_image_for_templates');

if (!function_exists('amnesty_disable_editor_on_chronicle_archive_page')) {
    /**
     * Removes the editor from the Chronicle Archives page template to prevent
     * editors from adding custom content.
     */
    function amnesty_disable_editor_on_chronicle_archive_page(): void
    {
        $post_id = $_GET['post'] ?? null;
        if (!$post_id) {
            return;
        }

        $template_file = get_post_meta($post_id, '_wp_page_template', true);

        if ('archive-chronique' === $template_file) {
            remove_post_type_support('page', 'editor');
        }
    }
}
add_action('load-post.php', 'amnesty_disable_editor_on_chronicle_archive_page');
