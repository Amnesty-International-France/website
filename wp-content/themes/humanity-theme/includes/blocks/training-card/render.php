<?php

if (!function_exists('render_training_card_block')) {
    function render_training_card_block($attributes): string
    {
        $post_to_render = null;
        if (!empty($attributes['postId'])) {
            $post_to_render = get_post((int) $attributes['postId']);
        } elseif (isset($GLOBALS['post'])) {
            $post_to_render = $GLOBALS['post'];
        }

        if (!$post_to_render) {
            return '';
        }

        $post_id = $post_to_render->ID;

        $title = get_the_title($post_to_render);
        $permalink = get_permalink($post_id);
        $thumbnail = get_the_post_thumbnail($post_id, 'medium', ['class' => 'training-card__image']);

        $lieu = get_field('lieu', $post_id);
        $city = get_field('city', $post_id);
        $is_members_only = get_field('members_only', $post_id);
        $category_value = get_field('categories', $post_id);

        $category_label = '';
        if ($category_value) {
            $field_obj = get_field_object('field_688344d2380a3');
            if ($field_obj && isset($field_obj['choices'][$category_value])) {
                $category_label = $field_obj['choices'][$category_value];
            }
        }

        $args = [
            'title'            => $title,
            'permalink'        => $permalink,
            'thumbnail'        => $thumbnail,
            'lieu'             => $lieu,
            'city'             => $city,
            'is_members_only'  => $is_members_only,
            'category_label'   => $category_label,
            'session_start'	   => $attributes['session_start'],
            'session_end'	   => $attributes['session_end'],
        ];

        ob_start();
        $template_path = locate_template('partials/training-card.php');
        if ($template_path) {
            extract($args);
            include $template_path;
        }
        return ob_get_clean();
    }
}
