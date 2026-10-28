<?php

declare(strict_types=1);

/*
 * Stand-ins shared by the Petitions tests that load includes/post-types/petitions.php
 * with an active CLH campaign. Tests seed the campaign in $GLOBALS['__phpunit_clh_campaign']
 * and its petitions in $GLOBALS['__phpunit_clh_petitions'].
 */

if (!class_exists('WP_Post')) {
    eval('class WP_Post { public int $ID = 0; public int $post_parent = 0; public string $post_title = ""; }');
}

if (!class_exists('WP_Query')) {
    eval('class WP_Query { public array $posts; public function __construct(array $args) { $this->posts = $GLOBALS["__phpunit_clh_petitions"]; } }');
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return false;
    }
}

if (!function_exists('amnesty_get_clh_petition_campaign_page')) {
    function amnesty_get_clh_petition_campaign_page(): ?WP_Post
    {
        return $GLOBALS['__phpunit_clh_campaign'] ?? null;
    }
}

if (!function_exists('amnesty_get_active_clh_campaign_for_page')) {
    function amnesty_get_active_clh_campaign_for_page(int $page_id): ?WP_Post
    {
        return $GLOBALS['__phpunit_clh_campaign'] ?? null;
    }
}
