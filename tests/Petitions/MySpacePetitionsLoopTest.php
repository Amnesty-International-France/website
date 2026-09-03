<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (!class_exists('WP_Query')) {
    class WP_Query
    {
        /** @var array<int,self> */
        public static array $instances = [];

        /** @var array<string,mixed> */
        public array $args;

        public int $max_num_pages = 0;

        /** @param array<string,mixed> $args */
        public function __construct(array $args)
        {
            $this->args = $args;
            self::$instances[] = $this;
        }

        public function have_posts(): bool
        {
            return false;
        }

        public function the_post(): void
        {
        }
    }
}

if (!function_exists('get_query_var')) {
    $GLOBALS['__phpunit_query_vars'] = [];

    function get_query_var(string $key, mixed $default = ''): mixed
    {
        return $GLOBALS['__phpunit_query_vars'][$key] ?? $default;
    }
}

if (!function_exists('current_time')) {
    function current_time(string $type): string
    {
        return date($type);
    }
}

if (!function_exists('the_title')) {
    function the_title(): void
    {
        echo 'Pétitions';
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return $url;
    }
}

if (!function_exists('get_pagenum_link')) {
    function get_pagenum_link(int $page): string
    {
        return 'https://example.test/page/' . $page;
    }
}

if (!function_exists('paginate_links')) {
    $GLOBALS['__phpunit_paginate_links_args'] = [];

    function paginate_links(array $args): string
    {
        $GLOBALS['__phpunit_paginate_links_args'][] = $args;

        return '';
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('render_block')) {
    function render_block(array $block): string
    {
        return '';
    }
}

if (!function_exists('wp_reset_postdata')) {
    function wp_reset_postdata(): void
    {
    }
}

final class MySpacePetitionsLoopTest extends TestCase
{
    protected function setUp(): void
    {
        WP_Query::$instances = [];
        $GLOBALS['__phpunit_query_vars'] = [];
        $GLOBALS['__phpunit_paginate_links_args'] = [];
        unset($GLOBALS['is_my_space_petitions_loop']);
    }

    public function testQueriesActivePetitionsOrderedByClosestEndDate(): void
    {
        $GLOBALS['__phpunit_query_vars']['paged'] = 2;

        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/petitions-loop.php';
        ob_end_clean();

        self::assertCount(1, WP_Query::$instances);

        $args = WP_Query::$instances[0]->args;

        self::assertSame('petition', $args['post_type']);
        self::assertSame(18, $args['posts_per_page']);
        self::assertSame(2, $args['paged']);
        self::assertSame('date_de_fin', $args['meta_key']);
        self::assertSame('DATE', $args['meta_type']);
        self::assertSame('meta_value', $args['orderby']);
        self::assertSame('ASC', $args['order']);
        self::assertSame(
            [
                [
                    'key' => 'date_de_fin',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE',
                ],
            ],
            $args['meta_query']
        );
        self::assertArrayNotHasKey('is_my_space_petitions_loop', $GLOBALS);
    }
}
