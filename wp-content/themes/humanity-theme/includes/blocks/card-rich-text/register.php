<?php

declare(strict_types=1);

if (!function_exists('register_card_rich_text_block')) {
    /**
     * Register the Card Rich Text block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_card_rich_text_block(): void
    {
        register_block_type('amnesty-core/card-rich-text', [
            'api_version' => 3,
            'render_callback' => 'render_card_rich_text_block',
            'attributes' => [
                'thumbnail' => [
                    'type' => ['integer', 'null'],
                    'default' => null,
                ],
                'surtitle' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'title' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'permalink' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'linkTitle' => [
                    'type' => 'string',
                    'default' => 'Voir la suite',
                ],
                'newTab' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);
    }
}
