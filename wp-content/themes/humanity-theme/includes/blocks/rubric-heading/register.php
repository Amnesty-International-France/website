<?php

declare(strict_types=1);

/**
 * Rubric Heading Block Registration.
 *
 * @package Amnesty\Blocks
 */

if (!function_exists('register_rubric_heading_block')) {
    function register_rubric_heading_block(): void
    {
        register_block_type('amnesty/rubric-heading');
    }
}
