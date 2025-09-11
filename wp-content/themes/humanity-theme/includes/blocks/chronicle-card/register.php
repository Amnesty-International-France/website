<?php

declare(strict_types=1);

if (!function_exists('register_chronicle_card_block')) {
    function register_chronicle_card_block(): void
    {
        register_block_type(
            'amnesty-core/chronicle-card',
            [
                'render_callback' => 'render_chronicle_card_block',
            ]
        );
    }
}
