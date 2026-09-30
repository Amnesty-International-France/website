<?php

declare(strict_types=1);

/**
 * Request-scoped in-memory cache for block templates.
 *
 * `get_block_templates()` re-reads the `wp_template` posts on every call, which happens
 * dozens of times per admin page load.
 *
 * Note: on a cache hit, the `get_block_templates` filter is skipped. A filter that depends
 * on runtime state (current user, post, locale) will keep its first result for the request.
 *
 * @package Amnesty\ThemeSetup
 */
(static function (): void {
    $block_templates_cache = [];

    $cache_key = static function (array $query, string $template_type): string {
        unset($query['slug__not_in']);
        return md5(serialize([$query, $template_type]));
    };

    add_filter(
        'pre_get_block_templates',
        static function ($templates, array $query, string $template_type) use (&$block_templates_cache, $cache_key) {
            if (null !== $templates) {
                return $templates;
            }

            $key = $cache_key($query, $template_type);

            if (!isset($block_templates_cache[$key])) {
                return null;
            }

            return array_map(
                static fn (WP_Block_Template $template): WP_Block_Template => clone $template,
                $block_templates_cache[$key]
            );
        },
        PHP_INT_MAX,
        3
    );

    add_filter(
        'get_block_templates',
        static function ($templates, array $query, string $template_type) use (&$block_templates_cache, $cache_key) {
            if (!is_array($templates)) {
                return $templates;
            }

            $key = $cache_key($query, $template_type);
            $block_templates_cache[$key] = $templates;

            return $templates;
        },
        PHP_INT_MAX,
        3
    );

    add_action(
        'clean_post_cache',
        static function (int $post_id, WP_Post $post) use (&$block_templates_cache): void {
            if (!in_array($post->post_type, ['wp_template', 'wp_template_part'], true)) {
                return;
            }

            $block_templates_cache = [];
        },
        10,
        2
    );
})();
