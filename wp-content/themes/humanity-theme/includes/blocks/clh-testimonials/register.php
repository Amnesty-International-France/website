<?php

declare(strict_types=1);

if (!function_exists('register_clh_testimonials_block')) {
    /**
     * Register the "Ils vous disent merci" block of the CLH page
     *
     * @return void
     */
    function register_clh_testimonials_block(): void
    {
        register_block_type('amnesty-core/clh-testimonials', [
            'render_callback' => 'render_clh_testimonials_block',
            'attributes' => [
                'title' => ['type' => 'string', 'default' => __('Ils vous disent merci', 'amnesty')],
                'subtitle' => ['type' => 'string', 'default' => ''],
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
