<?php

declare(strict_types=1);

/**
 * Content Callout Block Registration.
 *
 * @package Amnesty\Blocks
 */

if (!function_exists('register_content_callout_block')) {
    function register_content_callout_block(): void
    {
        register_block_type('amnesty/content-callout', [
            'render_callback' => 'amnesty_render_content_callout_block',
        ]);
    }
}
