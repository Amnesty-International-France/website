<?php

declare(strict_types=1);

if (!function_exists('register_clh_testimonials_block')) {
    /**
     * Register the "Ils vous disent merci" block of the CLH page
     *
     * Editor side only: the block has no front-end display yet.
     *
     * @return void
     */
    function register_clh_testimonials_block(): void
    {
        register_block_type('amnesty-core/clh-testimonials', [
            'attributes' => [
                'testimonials' => [
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
