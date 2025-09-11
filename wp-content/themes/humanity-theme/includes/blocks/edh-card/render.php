<?php

declare(strict_types=1);

if (!function_exists('render_edh_card_block')) {
    function render_edh_card_block($attributes): string
    {
        $direction     = $attributes['direction'] ?? 'portrait';

        $post_to_render = null;
        if (!empty($attributes['postId'])) {
            $post_to_render = get_post((int)$attributes['postId']);
        } elseif (isset($GLOBALS['post'])) {
            $post_to_render = $GLOBALS['post'];
        }

        if (!$post_to_render) {
            return '';
        }

        $post_id = $post_to_render->ID;

        $title = get_the_title($post_to_render);
        $permalink = get_permalink($post_to_render);
        $thumbnail = get_the_post_thumbnail($post_id, 'medium', ['class' => 'edh-card__image']);

        $content_type = get_field('content_type', $post_id);
        $theme = get_field('theme', $post_id);
        $requirements = get_field('requirements', $post_id);
        $activity_duration = get_field('activity_duration', $post_id);

        $content_type_label = '';
        $theme_label = '';
        $requirements_label = '';
        $activity_duration_label = '';

        if ($content_type) {
            $field_obj_ct = get_field_object('field_6892103b14459');

            if ($field_obj_ct && isset($field_obj_ct['choices'][$content_type])) {
                $content_type_label = $field_obj_ct['choices'][$content_type];
            }
        }

        if ($theme) {
            $field_obj_theme = get_field_object('field_689210a11445a');

            if ($field_obj_theme && isset($field_obj_theme['choices'][$content_type])) {
                $theme_label = $field_obj_theme['choices'][$content_type];
            }
        }

        if ($requirements) {
            $field_obj_requirements = get_field_object('field_689210be1445b');

            if ($field_obj_requirements && isset($field_obj_requirements['choices'][$content_type])) {
                $requirements_label = $field_obj_requirements['choices'][$content_type];
            }
        }

        if ($activity_duration) {
            $field_obj_activity_duration = get_field_object('field_689210f30c5c4');

            if ($field_obj_activity_duration && isset($field_obj_activity_duration['choices'][$content_type])) {
                $activity_duration_label = $field_obj_activity_duration['choices'][$content_type];
            }
        }

        $args = [
            'title' => $title,
            'permalink' => $permalink,
            'thumbnail' => $thumbnail,
            'content_type' => $content_type,
            'theme' => $theme,
            'requirements' => $requirements,
            'activity_duration' => $activity_duration,
        ];

        ob_start();
        $template_path = locate_template('partials/edh-card.php');
        if ($template_path) {
            extract($args);
            include $template_path;
        }
        return ob_get_clean();
    }
}
