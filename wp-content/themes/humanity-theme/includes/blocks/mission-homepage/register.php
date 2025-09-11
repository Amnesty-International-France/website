<?php

declare(strict_types=1);

if (!function_exists('register_mission_homepage_block')) {
    /**
     * Register the Mission Homepage block
     */
    function register_mission_homepage_block(): void
    {
        register_block_type(
            'amnesty-core/mission-homepage',
            [
                'render_callback' => 'render_mission_homepage_block',
            ]
        );

    }
}
