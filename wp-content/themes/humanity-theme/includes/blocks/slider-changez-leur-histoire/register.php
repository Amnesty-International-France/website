<?php

declare(strict_types=1);

if (!function_exists('register_slider_changez_leur_histoire_block')) {
    /**
     * Register the "Slider Changez leur histoire" block.
     */
    function register_slider_changez_leur_histoire_block(): void
    {
        register_block_type('amnesty-core/slider-changez-leur-histoire', [
            'render_callback' => 'render_slider_changez_leur_histoire_block',
            'attributes'      => [
                'petitionType' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'selectedPosts' => [
                    'type'    => 'array',
                    'default' => [],
                    'items'   => [
                        'type' => 'object',
                    ],
                ],
            ],
        ]);
    }
}
