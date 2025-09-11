<?php

declare(strict_types=1);

if (! function_exists('register_urgent_register_form_block')) {
    /**
     * Register Urgent Register Form Block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_urgent_register_form_block(): void
    {
        register_block_type(
            'amnesty-core/urgent-register-form',
            [
                'render_callback' => 'render_urgent_register_form_block',
                'attributes'      => [
                    'text_header' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'input'       => [
                        'type'    => 'array',
                        'default' => [],
                    ],
                    'action_type' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );
    }
}
