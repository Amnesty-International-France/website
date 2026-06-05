<?php

declare(strict_types=1);

if (! function_exists('register_au_thematic_register_form_block')) {
    /**
     * Register AU Thematic Register Form Block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_au_thematic_register_form_block(): void
    {
        register_block_type(
            'amnesty-core/au-thematic-register-form',
            [
                'render_callback' => 'render_au_thematic_register_form_block',
                'attributes'      => [
                    'textHeader' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'input'       => [
                        'type'    => 'array',
                        'default' => ['email'],
                    ],
                    'actionType' => [
                        'type'    => 'string',
                        'default' => 'Email',
                    ],
                    'thematique' => [
                        'type' => 'string',
                        'default' => '',
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
