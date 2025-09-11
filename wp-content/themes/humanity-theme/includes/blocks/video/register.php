<?php

declare(strict_types=1);

if (!function_exists('register_video_block')) {
    /**
     * Register the Chapo block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_video_block(): void
    {
        register_block_type(
            'amnesty-core/video',
            [
                'render_callback' => 'render_video_block',
                'attributes' => [
                    'url' => [
                        'type' => 'string',
                    ],
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );
    }
}
