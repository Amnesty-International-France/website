<?php

declare(strict_types=1);

if (!function_exists('register_call_to_action_block')) {
    /**
     * Register the CTA block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_call_to_action_block(): void
    {
        register_block_type('amnesty-core/call-to-action', [
            'render_callback' => 'render_call_to_action_block',
            'attributes' => [
                'direction' => [
                    'type' => 'string',
                    'default' => 'horizontal',
                ],
                'title' => [
                    'type' => 'string',
                    'default' => 'Title',
                ],
                'subTitle' => [
                    'type' => 'string',
                    'default' => 'Subtitle',
                ],
                'buttonLabel' => [
                    'type' => 'string',
                    'default' => 'Button Label',
                ],
                'linkType' => [
                    'type' => 'string',
                    'default' => 'external',
                ],
                'internalUrl' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'externalUrl' => [
                    'type' => 'string',
                    'default' => '#',
                ],
                'postId' => [
                    'type' => 'number',
                ],
                'internalUrlTitle' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'targetBlank' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ]);
    }
}
