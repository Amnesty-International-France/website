<?php

declare(strict_types=1);

if (!function_exists('register_training_card_block')) {
    function register_training_card_block(): void
    {
        register_block_type(
            'amnesty-core/training-card',
            [
                'render_callback' => 'render_training_card_block',
                'attributes' => [
                    'postId' => [
                        'type' => 'integer',
                        'default' => null,
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
                    'lieu' => [
                        'type' => 'string',
                        'default' => '',
                    ],
					'city' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'date' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'category_label' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'members_only' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
            ]
        );
    }
}
