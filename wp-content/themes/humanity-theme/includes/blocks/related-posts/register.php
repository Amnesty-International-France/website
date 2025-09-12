<?php

declare(strict_types=1);

if (!function_exists('register_related_posts_block')) {
    /**
     * Register Related Posts Block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_related_posts_block(): void
    {
        register_block_type(
            'amnesty-core/related-posts',
            [
                'render_callback' => 'render_related_posts_block',
                'attributes'      => [
                    'title' => [
                        'type' => 'string',
                        'default' => 'À lire aussi',
                    ],
                    'nb_posts' => [
                        'type' => 'number',
                        'default' => 3,
                    ],
                    'display' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'postIds' => [
                        'type'    => 'array',
                        'items'   => [ 'type' => 'number' ],
                        'default' => [],
                    ],
                    'fallback_category_slug' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );
    }
}
