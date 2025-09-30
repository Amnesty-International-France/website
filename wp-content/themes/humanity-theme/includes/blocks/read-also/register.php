<?php

declare(strict_types=1);

if (!function_exists('register_read_also_block')) {
    /**
     * Register the Read Also block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_read_also_block(): void
    {
        register_block_type(
            'amnesty-core/read-also',
            [
                'render_callback' => 'render_read_also_block',
                'attributes'      => [
                    'linkType' => [
                        'type'    => 'string',
                        'default' => 'internal',
                    ],
                    'externalUrl' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'externalLabel' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'postId' => [
                        'type' => 'number',
                    ],
                    'postType' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'internalUrl' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'internalUrlTitle' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'targetBlank' => [
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                ],
            ]
        );
    }
}
