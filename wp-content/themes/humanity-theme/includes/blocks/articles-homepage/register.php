<?php

declare(strict_types=1);

if (!function_exists('register_articles_homepage_block')) {
    /**
     * Register the Articles Homepage block
     */
    function register_articles_homepage_block(): void
    {
        register_block_type(
            'amnesty-core/articles-homepage',
            [
                'render_callback' => 'render_articles_homepage_block',
                'attributes' => [
                    'items' => [
                        'type' => 'array',
                        'default' => [
                            [
                                'category' => 'actualites',
                                'selectedPostId' => null,
                                'subtitle' => '',
                                'externalPost' => false,
                                'externalItemLink' => '',
                                'externalItemTitle' => '',
                                'externalItemImgId' => null,
                                'externalItemImgUrl' => '',
                            ],
                            [
                                'category' => 'dossiers',
                                'selectedPostId' => null,
                                'subtitle' => '',
                                'externalPost' => false,
                                'externalItemLink' => '',
                                'externalItemTitle' => '',
                                'externalItemImgId' => null,
                                'externalItemImgUrl' => '',
                            ],
                            [
                                'category' => 'reperes',
                                'selectedPostId' => null,
                                'subtitle' => '',
                                'externalPost' => false,
                                'externalItemLink' => '',
                                'externalItemTitle' => '',
                                'externalItemImgId' => null,
                                'externalItemImgUrl' => '',
                            ],
                        ],
                    ],
                ],
            ]
        );

    }
}
