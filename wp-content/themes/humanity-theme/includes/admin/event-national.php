<?php

declare(strict_types=1);

add_action(
    'acf/include_fields',
    function () {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(
            [
                'key'                   => 'group_685bfd654d813',
                'title'                 => 'Partout En France',
                'fields'                => [
                    [
                        'key'               => 'field_685bfd654bfce',
                        'label'             => 'Rendre l\'évènement national',
                        'name'              => '_EventNational',
                        'aria-label'        => '',
                        'type'              => 'true_false',
                        'instructions'      => '',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => [
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ],
                        'message'           => '',
                        'default_value'     => 0,
                        'allow_in_bindings' => 0,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                        'ui'                => 1,
                    ],
                ],
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'tribe_events',
                        ],
                    ],
                ],
                'menu_order'            => -100,
                'position'              => 'side',
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

add_action('save_post', 'set_event_national', 20, 1);

function set_event_national($post_id): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $event = get_post($post_id);

    $is_national = get_field('event_national', $post_id);

    if ($is_national) {
        update_post_meta($post_id, 'event_national', $event->ID);
    }
}
