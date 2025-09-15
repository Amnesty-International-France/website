<?php

declare(strict_types=1);

if (! function_exists('register_archive_filters_militants_resources_block')) {
    /**
     * Register the archive filters militants resources block
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function register_archive_filters_militants_resources_block(): void
    {
        register_block_type_from_metadata(
            __DIR__,
            [
                'render_callback' => 'render_archive_filters_militants_resources_block',
            ],
        );
    }
}
