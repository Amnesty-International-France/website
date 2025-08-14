<?php

declare(strict_types=1);

if (!function_exists('register_action_block')) {
    /**
     * Register the Action block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_action_block(): void
    {
        register_block_type('amnesty-core/action', [
            'render_callback' => 'render_action_block',
            'attributes' => [
                'type' => [
                    'type' => 'string',
                    'default' => 'petition',
                ],
                'surTitle' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'title' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'description' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'imageUrl' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'buttonText' => [
                    'type' => 'string',
                    'default' => 'En savoir plus',
                ],
                'buttonLink' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'buttonPosition' => [
                    'type' => 'string',
                    'default' => 'left',
                ],
                'backgroundColor' => [
                    'type' => 'string',
                    'default' => 'primary',
                ],
                'petitionId' => [
                    'type' => 'number',
                ],
                'petitionData' => [
                    'type' => 'object',
                ],
                'overrideTitle' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'overrideImageUrl' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ]);
    }
}
