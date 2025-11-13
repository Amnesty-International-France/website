<?php

declare(strict_types=1);

if (!function_exists('register_card_image_text_block')) {
    /**
     * Register the Card Image Text block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_card_image_text_block(): void
    {
        register_block_type('amnesty-core/card-image-text', [
            'api_version' => 3,
            'render_callback' => 'render_card_image_text_block',
            'attributes' => [
                'editor' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'custom' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'direction' => [
                    'type' => 'string',
                    'default' => 'vertical',
                ],
                'postId' => [
                    'type' => ['integer', 'null', 'string'],
                    'default' => null,
                ],
                'title' => [
                    'type' => 'string',
                    'default' => 'Titre par défaut',
                ],
                'subtitle' => [
                    'type' => 'string',
                    'default' => 'Sous-titre par défaut',
                ],
                'category' => [
                    'type' => 'string',
                    'default' => 'Categorie',
                ],
                'permalink' => [
                    'type' => 'string',
                    'default' => '#',
                ],
                'thumbnail' => [
                    'type' => ['integer', 'null', 'string'],
                    'default' => null,
                ],
                'text' => [
                    'type' => 'string',
                    'default' => 'Texte par défaut',
                ],
                'newTab' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);
    }
}
