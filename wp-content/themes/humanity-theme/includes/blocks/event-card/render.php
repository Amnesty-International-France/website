<?php

declare(strict_types=1);

if (! function_exists('render_event_card_block')) {

    /**
     * Render Event Card Block
     *
     * @param array<string,mixed> $attributes the block attributes
     *
     * @return string
     * @package Amnesty\Blocks
     */
    function render_event_card_block($attributes, $content, $block): string
    {
        $direction     = $attributes['direction'] ?? 'portrait';
        $thumbnail     = $attributes['thumbnail'] ?? '';
        $date          = $attributes['date'] ?? date('Y-m-d');
        $title         = $attributes['title'] ?? 'Titre par défaut';
        $permalink     = $attributes['permalink'] ?? '#';
        $post_terms    = $attributes['terms'] ?? [];
        $main_category = $attributes['main_category'] ?? null;
        $is_custom     = $attributes['is_custom'] ?? false;

        if ($is_custom) {
            if (is_string($main_category)) {
                $main_category = (object) [
                    'name'     => $main_category,
                    'slug'     => sanitize_title($main_category),
                    'taxonomy' => 'category',
                    'term_id'  => 0,
                ];
            } elseif (is_array($main_category)) {
                $main_category = (object) $main_category;
            }

            if (is_numeric($thumbnail)) {
                $thumbnail = wp_get_attachment_image((int) $thumbnail, 'medium', false, [ 'class' => 'event-image' ]);
            }

            $args = [
                'direction'     => $direction,
                'title'         => $title,
                'permalink'     => $permalink,
                'date'          => $date,
                'thumbnail'     => $thumbnail,
                'main_category' => $main_category,
                'terms'         => $post_terms,
            ];
        } else {
            if (! empty($attributes['postId']) && ! is_admin()) {
                $post = get_post((int) $attributes['postId']);
                if ($post) {
                    setup_postdata($post);
                }
            } elseif (empty($attributes['postId']) && isset($GLOBALS['post'])) {
                $post = $GLOBALS['post'];
            }

            if (isset($post) && $post) {
                $post_id       = $post->ID;
                $permalink     = get_permalink($post);
                $title         = get_the_title($post);
                $date          = get_the_date('', $post);
                $thumbnail     = get_the_post_thumbnail($post->ID, 'medium', [ 'class' => 'event-image' ]);
                $post_terms    = wp_get_object_terms($post->ID, get_object_taxonomies(get_post_type($post)));
                $main_category = amnesty_get_a_post_term($post->ID);
            }

            if (! empty($attributes['postId']) && ! is_admin()) {
                wp_reset_postdata();
            }

            $args = [
                'direction'     => $direction,
                'title'         => $title,
                'permalink'     => $permalink,
                'date'          => $date,
                'thumbnail'     => $thumbnail,
                'main_category' => $main_category,
                'terms'         => $post_terms,
            ];

            if (isset($post_id)) {
                $args['post_id'] = $post_id;
            }
        }

        ob_start();
        $template_path = locate_template('partials/event-card.php');
        if ($template_path) {
            extract($args);
            include $template_path;
        } else {
            error_log('❌ Template "partials/event-card.php" introuvable');
        }
        return ob_get_clean();
    }
}
