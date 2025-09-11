<?php

declare(strict_types=1);

if (! function_exists('render_edh_card_block')) {
    function render_edh_card_block($attributes): string
    {
        $direction = $attributes['direction'] ?? 'portrait';

        $post_to_render = null;
        if (! empty($attributes['postId'])) {
            $post_to_render = get_post((int) $attributes['postId']);
        } elseif (isset($GLOBALS['post'])) {
            $post_to_render = $GLOBALS['post'];
        }

        if (! $post_to_render) {
            return '';
        }

        $post_id = $post_to_render->ID;

        $title     = get_the_title($post_to_render);
        $permalink = get_permalink($post_to_render);
        $thumbnail = get_the_post_thumbnail($post_id, 'medium', [ 'class' => 'edh-card__image' ]);

        $content_type      = get_field('type_de_contenu', $post_id);
        $theme             = get_field('theme', $post_id);
        $requirements      = get_field('requirements', $post_id);
        $activity_duration = get_field('activity_duration', $post_id);

        $content_type_label      = '';
        $theme_label             = '';
        $requirements_label      = '';
        $activity_duration_label = '';

        if ($content_type) {
            $field_obj_ct = get_field_object('field_68c2a14b84414');

            if ($field_obj_ct && isset($field_obj_ct['choices'][ $content_type ])) {
                $content_type_label = $field_obj_ct['choices'][ $content_type ];
            }
        }

        if ($theme) {
            $field_obj_theme = get_field_object('field_68c2a1a484415');

            if ($field_obj_theme && isset($field_obj_theme['choices'][ $theme ])) {
                $theme_label = $field_obj_theme['choices'][ $theme ];
            } else {
                $key = array_search($theme, $field_obj_theme['choices'], true);
                $theme_label = $field_obj_theme['choices'][ $key ];
            }
        }


        if ($requirements) {
            $field_obj_requirements = get_field_object('field_68c2a1f984416');

            if ($field_obj_requirements && isset($field_obj_requirements['choices'][ $requirements ])) {
                $requirements_label = $field_obj_requirements['choices'][ $requirements ];
            } else {
                $key = array_search($theme, $field_obj_requirements['choices'], true);
                $theme_label = $field_obj_requirements['choices'][ $key ];
            }
        }

        if ($activity_duration) {
            $field_obj_activity_duration = get_field_object('field_68c2a22184417');

            if ($field_obj_activity_duration && isset($field_obj_activity_duration['choices'][ $activity_duration ])) {
                $key                     = array_search($activity_duration, $field_obj_activity_duration['choices'], true);
                $activity_duration_label = $field_obj_activity_duration['choices'][ $activity_duration ];

            } else {
                $key = array_search($theme, $field_obj_activity_duration['choices'], true);
                $theme_label = $field_obj_activity_duration['choices'][ $key ];
            }
        }

        $args = [
            'title'             => $title,
            'permalink'         => $permalink,
            'thumbnail'         => $thumbnail,
            'content_type'      => $content_type_label,
            'theme'             => $theme_label,
            'requirements'      => $requirements_label,
            'activity_duration' => $activity_duration_label,
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
