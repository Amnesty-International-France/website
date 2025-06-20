<?php
declare(strict_types=1);

if (!function_exists('register_agenda_homepage_block')) {
    /**
     * Register the Agenda Homepage block.
     */
    function register_agenda_homepage_block(): void {
        register_block_type(
            'amnesty-core/agenda-homepage',
            [
                'render_callback' => 'render_agenda_homepage_block',
                'attributes'      => [         
                    'selectionMode' => [
                        'type'    => 'string',
                        'default' => 'latest',
                    ],
                    'selectedEventIds' => [
                        'type'    => 'array',
                        'default' => [],
                        'items'   => [
                            'type' => 'number',
                        ],
                    ],
                    'firstEventId' => [
                        'type'    => 'number',
                        'default' => 0,
                    ],
                    'secondEventId' => [
                        'type'    => 'number',
                        'default' => 0,
                    ],
                    'chronicleImageUrl' => [
                        'type'    => 'string',
                        'default' => '',
                    ],
                    'chronicleImageId' => [
                        'type'    => 'number',
                    ],
                ],
            ]
        );
    }
}
