<?php

declare(strict_types=1);

if (!function_exists('register_button_block')) {
    /**
     * Register the Button block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_button_block(): void
    {
        register_block_type('amnesty-core/button', [
            'render_callback' => 'render_button_block',
            'attributes' => [
                'postId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'postType' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'internalUrl' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'internalUrlTitle' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'label' => [
                    'type' => 'string',
                    'default' => 'Bouton',
                ],
                'size' => [
                    'type' => 'string',
                    'default' => 'medium',
                ],
                'style' => [
                    'type' => 'string',
                    'default' => 'bg-yellow',
                ],
                'icon' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'link' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'linkType' => [
                    'type' => 'string',
                    'default' => 'internal',
                ],
                'externalUrl' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'targetBlank' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'alignment' => [
                    'type' => 'string',
                    'default' => 'left',
                ],
            ],
        ]);
    }
}
