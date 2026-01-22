<?php

declare(strict_types=1);

if (!function_exists('register_change_their_history_toc_block')) {
    /**
     * Register the Change Their History TOC block.
     */
    function register_change_their_history_toc_block(): void
    {
        register_block_type('amnesty-core/change-their-history-toc', [
            'render_callback' => 'render_change_their_history_toc_block',
            'attributes'      => [],
        ]);
    }
}
