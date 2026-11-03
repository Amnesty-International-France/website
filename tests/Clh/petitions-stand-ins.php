<?php

declare(strict_types=1);

/*
 * Stand-ins for the lookups of includes/post-types/petitions.php, which cannot be loaded
 * without WordPress and cache their results for the whole run: each one returns what the
 * test seeded in $GLOBALS. Shared by the Clh suite, so every test file gets the same ones
 * whatever the load order.
 */

if (!function_exists('amnesty_get_clh_petition_campaign_page')) {
    function amnesty_get_clh_petition_campaign_page(): ?object
    {
        return $GLOBALS['__phpunit_clh_campaign_page'] ?? null;
    }
}

if (!function_exists('amnesty_is_clh_petition_tunnel_active')) {
    function amnesty_is_clh_petition_tunnel_active(): bool
    {
        return $GLOBALS['__phpunit_clh_tunnel_active'] ?? false;
    }
}

if (!function_exists('amnesty_get_clh_campaign_petitions')) {
    function amnesty_get_clh_campaign_petitions(): array
    {
        return $GLOBALS['__phpunit_clh_campaign_petitions'] ?? [];
    }
}

if (!function_exists('amnesty_get_clh_petition_tunnel_url')) {
    function amnesty_get_clh_petition_tunnel_url(): string
    {
        return $GLOBALS['__phpunit_clh_tunnel_url'] ?? '';
    }
}

if (!function_exists('amnesty_get_petition_signature_count')) {
    function amnesty_get_petition_signature_count($post_id): int
    {
        return $GLOBALS['__phpunit_clh_signature_counts'][$post_id] ?? 0;
    }
}
