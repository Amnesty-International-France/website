<?php

declare(strict_types=1);

if (!function_exists('register_carousel_block')) {
    /**
     * Register the carousel block
     *
     * @return void
     */
    function register_carousel_block(): void {
        register_block_type('amnesty-core/carousel', [
            'render_callback' => 'render_carousel_block',
            'attributes'      => [
                'images' => [
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
