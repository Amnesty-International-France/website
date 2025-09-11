<?php

declare(strict_types=1);

if (!function_exists('register_key_figure_block')) {
    /**
     * Register the Key Figure block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_key_figure_block(): void
    {
        register_block_type(
            'amnesty-core/key-figure',
        );
    }
}
