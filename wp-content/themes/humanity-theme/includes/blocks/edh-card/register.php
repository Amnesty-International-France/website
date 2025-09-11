<?php

declare(strict_types=1);

if (!function_exists('register_edh_card_block')) {
    function register_edh_card_block(): void
    {
        register_block_type(
            'amnesty-core/edh-card',
            [
                'render_callback' => 'render_edh_card_block',
                'attributes' => [
                    'postId' => [
                        'type' => 'integer',
                        'default' => null,
                    ],
                    'direction'     => [
                        'type'    => 'string',
                        'default' => 'portrait',
                    ],
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'permalink' => [
                        'type' => 'string',
                        'default' => '#',
                    ],
                    'thumbnail' => [
                        'type' => ['integer', 'string'],
                        'default' => '',
                    ],
                    'content_type' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'theme' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'requirements' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'activity_duration' => [
                        'type' => 'string',
                        'default' => '',
                    ]
                ],
            ]
        );
    }
}
