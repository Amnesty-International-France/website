<?php

declare(strict_types=1);

if (!function_exists('render_document_card_block')) {

    /**
     * Render Document Card Block
     *
     * @param array<string,mixed> $attributes the block attributes
     *
     * @return string
     * @package Amnesty\Blocks
     *
     */
    function render_document_card_block($attributes, $content, $block): string
    {
        $direction = $attributes['direction'] ?? 'portrait';

        if (!empty($attributes['postId']) && !is_admin()) {
            $post = get_post((int) $attributes['postId']);
            if ($post) {
                setup_postdata($post);
            }
        } elseif (empty($attributes['postId']) && isset($GLOBALS['post'])) {
            $post = $GLOBALS['post'];
        }

        if (isset($post) && $post) {
            $post_id = $post->ID;
            $attachment = get_field('upload_du_document', $post_id);
            $permalink = wp_get_attachment_url($attachment['ID'] ?? 0);
            $title = get_the_title($post);
            $date = get_the_date('', $post);
            $thumbnail = get_the_post_thumbnail($post->ID, 'medium', ['class' => 'article-image']);
            $post_terms = wp_get_object_terms($post->ID, get_object_taxonomies(get_post_type($post)));
        }

        if (!empty($attributes['postId']) && ! is_admin()) {
            wp_reset_postdata();
        }

        $args = [
            'direction' => $direction,
            'title' => $title,
            'permalink' => $permalink,
            'date' => $date,
            'thumbnail' => $thumbnail,
            'terms' => $post_terms,
            'label' => 'Rapport',
        ];

        if (isset($post_id)) {
            $args['post_id'] = $post_id;
        }

        ob_start();
        $template_path = locate_template('partials/document-card.php');
        if ($template_path) {
            extract($args);
            include $template_path;
        }
        return ob_get_clean();
    }
}
