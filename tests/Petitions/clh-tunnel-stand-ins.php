<?php

declare(strict_types=1);

/*
 * Stand-ins shared by the Petitions tests that load includes/post-types/petitions.php
 * with an active CLH campaign. Tests seed the campaign in $GLOBALS['__phpunit_clh_campaign']
 * and its petitions in $GLOBALS['__phpunit_clh_petitions'].
 */

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

if (!function_exists('is_ssl')) {
    function is_ssl(): bool
    {
        return true;
    }
}

if (!class_exists('ClhRedirect')) {
    /** Thrown by the wp_redirect() stand-in, so tests can read where a handler redirects. */
    final class ClhRedirect extends RuntimeException
    {
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect(string $location, int $status = 302): never
    {
        throw new ClhRedirect($location);
    }
}

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
