<?php

declare(strict_types=1);

if (!function_exists('register_read_more_block')) {
    /**
     * Register the Read More block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_read_more_block()
    {
        register_block_type('amnesty-core/read-more', [
            'render_callback' => 'render_read_more_block',
        ]);
    }
}
