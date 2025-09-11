<?php

declare(strict_types=1);

if (!function_exists('register_section_home_block')) {
    /**
     * Register the Section Home block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_section_home_block(): void
    {
        register_block_type(
            'amnesty-core/section-home',
        );
    }
}
