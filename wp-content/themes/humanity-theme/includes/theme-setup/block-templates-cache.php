<?php

declare(strict_types=1);

(static function (): void {
    $block_templates_cache = [];

    add_filter(
        'pre_get_block_templates',
        static function ($templates, array $query, string $template_type) use (&$block_templates_cache) {
            if (null !== $templates) {
                return $templates;
            }

            $key = md5(serialize([$query, $template_type]));
            return $block_templates_cache[$key] ?? null;
        },
        PHP_INT_MAX,
        3
    );

    add_filter(
        'get_block_templates',
        static function (array $templates, array $query, string $template_type) use (&$block_templates_cache): array {
            $key = md5(serialize([$query, $template_type]));
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
