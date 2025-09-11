<?php

declare(strict_types=1);

if (!function_exists('register_actions_homepage_block')) {
    /**
     * Register the Actions Homepage block
     */
    function register_actions_homepage_block(): void
    {
        register_block_type(
            'amnesty-core/actions-homepage',
            [
                'render_callback' => 'render_actions_homepage_block',
                'attributes'      => [
                    'title' => [
                        'type' => 'string',
                        'default' => "S'engager",
                    ],
                    'chapo' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'items' => [
                        'type' => 'array',
                        'default' => [
                            [
                                'itemTitle' => 'Titre bloc 1',
                                'itemDescription' => 'Description du bloc 1',
                                'buttonLabel' => 'Label du bouton',
                                'buttonLink' => '#',
                                'imageUrl' => '',
                            ],
                            [
                                'itemTitle' => 'Titre bloc 2',
                                'itemDescription' => 'Description du bloc 2',
                                'buttonLabel' => 'Label du bouton',
                                'buttonLink' => '#',
                                'imageUrl' => '',
                            ],
                            [
                                'itemTitle' => 'Titre bloc 3',
                                'itemDescription' => 'Description du bloc 3',
                                'buttonLabel' => 'Label du bouton',
                                'buttonLink' => '#',
                                'imageUrl' => '',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
