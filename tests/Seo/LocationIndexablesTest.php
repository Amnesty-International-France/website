<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Scenario stubs for the WordPress and Yoast functions taxonomy-rewrite.php
 * calls. get_option() shares its store with CanonicalTest so either file can
 * be loaded first.
 */
if (! function_exists('get_option')) {
    $GLOBALS['__phpunit_options'] = [];

    function get_option(string $option, mixed $default = false): mixed
    {
        return $GLOBALS['__phpunit_options'][$option] ?? $default;
    }
}

if (! function_exists('update_option')) {
    function update_option(string $option, mixed $value): bool
    {
        $GLOBALS['__phpunit_options'][$option] = $value;

        return true;
    }
}

if (! function_exists('YoastSEO')) {
    $GLOBALS['__phpunit_yoast_permalink_resets'] = [];

    /**
     * Stub for the Yoast surface: only records the permalink resets requested
     * through YoastSEO()->helpers->indexable.
     */
    function YoastSEO(): object
    {
        $indexable = new class () {
            public function reset_permalink_indexables(?string $type = null, ?string $subtype = null): void
            {
                $GLOBALS['__phpunit_yoast_permalink_resets'][] = [ $type, $subtype ];
            }
        };

        return (object) [ 'helpers' => (object) [ 'indexable' => $indexable ] ];
    }
}

require_once __DIR__ . '/../../wp-content/themes/humanity-theme/includes/helpers/taxonomy-rewrite.php';

/**
 * MAINT-323: the location archives must drop the /pays/{slug}/ permalink Yoast
 * stored before the term_link filter existed.
 */
final class LocationIndexablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['__phpunit_options']                = [];
        $GLOBALS['__phpunit_yoast_permalink_resets'] = [];
    }

    public function testLocationPermalinksAreResetOnFirstRun(): void
    {
        $this->assertTrue(amnesty_reset_location_indexables());
        $this->assertSame([ [ 'term', 'location' ] ], $GLOBALS['__phpunit_yoast_permalink_resets']);
        $this->assertSame(AMNESTY_LOCATION_INDEXABLES_VERSION, get_option('amnesty_location_indexables_version'));
    }

    public function testResetRunsOnlyOnce(): void
    {
        amnesty_reset_location_indexables();

        $this->assertFalse(amnesty_reset_location_indexables());
        $this->assertCount(1, $GLOBALS['__phpunit_yoast_permalink_resets']);
    }

    public function testResetRunsAgainWhenTheVersionIsBumped(): void
    {
        $GLOBALS['__phpunit_options']['amnesty_location_indexables_version'] = '0';

        $this->assertTrue(amnesty_reset_location_indexables());
        $this->assertCount(1, $GLOBALS['__phpunit_yoast_permalink_resets']);
    }
}
