<?php
declare(strict_types=1);

if (!function_exists('register_slider_block')) {
    /**
     * Register the Slider block
     */
    function register_slider_block(): void {
        register_block_type('amnesty-core/slider', [
            'render_callback' => 'render_slider_block',
            'attributes'      => [
                'postType' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'selectedPosts' => [
                    'type'    => 'array',
                    'default' => [],
                    'items'   => [
                        'type' => 'object'
                    ]
                ],
            ],
        ]);
    }
}
