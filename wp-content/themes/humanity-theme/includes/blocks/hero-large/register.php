<?php

declare(strict_types=1);

if (! function_exists('register_hero_large_block')) {
    /**
     * Register the Hero large block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_hero_large_block(): void
    {
        register_block_type('amnesty-core/hero-large', [
            'render_callback' => 'render_hero_large_block',
            'attributes'      => [
                'titleFirstPart' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'titleLastPart' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'btnLinkText' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'btnLink' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'imagePostId' => [
                    'type'    => 'number',
                    'default' => null,
                ],
            ],
        ]);
    }
}

add_action('init', 'register_hero_large_block');
