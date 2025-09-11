<?php

declare(strict_types=1);

if (!function_exists('register_section_block')) {
    /**
     * Register the Section block
     *
     * @return void
     * @package Amnesty\Blocks
     *
     */
    function register_section_block(): void
    {
        register_block_type(
            'amnesty-core/section',
            [
                'render_callback' => 'render_section_block',
                'attributes' => [
                    'sectionSize' => [
                        'type' => 'string',
                        'default' => 'large',
                    ],
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'showTitle' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'fullWidth' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'contentSize' => [
                        'type' => 'string',
                        'default' => 'sm',
                    ],
                    'backgroundColor' => [
                        'type' => 'string',
                        'default' => 'black',
                    ],
                ]
            ]
        );
    }
}
