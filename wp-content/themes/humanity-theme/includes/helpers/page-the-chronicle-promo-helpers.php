<?php

declare(strict_types=1);

/**
 * Helper functions and tweaks for the Chronicle Promo page template.
 */

if (!function_exists('amnesty_disable_editor_on_promo_page')) {
    /**
     * Removes the editor from the Chronicle Promo page template to prevent
     * editors from adding custom content.
     */
    function amnesty_disable_editor_on_promo_page(): void
    {
        $post_id = $_GET['post'] ?? null;
        if (!$post_id) {
            return;
        }

        $template_file = get_post_meta($post_id, '_wp_page_template', true);

        if ('page-the-chronicle-promo' === $template_file) {
            remove_post_type_support('page', 'editor');
        }
    }
}
add_action('load-post.php', 'amnesty_disable_editor_on_promo_page');
