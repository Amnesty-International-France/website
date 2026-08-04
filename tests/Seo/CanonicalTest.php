<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Scenario stubs for the WordPress functions canonical.php calls. Values are
 * driven from the tests through $GLOBALS so each case can pick its own context.
 */
if (! function_exists('home_url')) {
    $GLOBALS['__phpunit_home_url'] = 'https://www.amnesty.fr';

    function home_url(string $path = '', ?string $scheme = null): string
    {
        $home = rtrim($GLOBALS['__phpunit_home_url'], '/');

        return '' === $path ? $home : $home . '/' . ltrim($path, '/');
    }
}

if (! function_exists('get_option')) {
    $GLOBALS['__phpunit_options'] = [];

    function get_option(string $option, mixed $default = false): mixed
    {
        return $GLOBALS['__phpunit_options'][$option] ?? $default;
    }
}

if (! function_exists('get_queried_object_id')) {
    $GLOBALS['__phpunit_queried_object_id'] = 0;

    function get_queried_object_id(): int
    {
        return (int) $GLOBALS['__phpunit_queried_object_id'];
    }
}

if (! function_exists('is_paged')) {
    $GLOBALS['__phpunit_is_paged'] = false;

    function is_paged(): bool
    {
        return (bool) $GLOBALS['__phpunit_is_paged'];
    }
}

if (! function_exists('current_url')) {
    $GLOBALS['__phpunit_current_url'] = 'https://www.amnesty.fr/';

    function current_url(): ?string
    {
        return $GLOBALS['__phpunit_current_url'];
    }
}

require_once __DIR__ . '/../../wp-content/themes/humanity-theme/includes/helpers/string-manipulation.php';
require_once __DIR__ . '/../../wp-content/themes/humanity-theme/includes/seo/canonical.php';

/**
 * MAINT-231: canonical URLs must be absolute, on the production host, with a
 * clean path - and archives must never lose their canonical.
 */
final class CanonicalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['__phpunit_home_url']          = 'https://www.amnesty.fr';
        $GLOBALS['__phpunit_options']           = [];
        $GLOBALS['__phpunit_queried_object_id'] = 0;
        $GLOBALS['__phpunit_is_paged']          = false;
        $GLOBALS['__phpunit_current_url']       = 'https://www.amnesty.fr/';
    }

    public function testForeignHostIsRewrittenOntoProduction(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/actualites/mon-article/',
            amnesty_normalise_canonical_host('https://amnesty.preview.infomaniak.website/actualites/mon-article/')
        );
    }

    public function testDotSegmentIsRemovedOnTheProductionHost(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/actualites/',
            amnesty_normalise_canonical_host('https://www.amnesty.fr/./actualites/')
        );
    }

    public function testDotSegmentIsRemovedWhileRewritingTheHost(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/actualites/',
            amnesty_normalise_canonical_host('https://amnesty.preview.infomaniak.website/./actualites/')
        );
    }

    public function testDuplicateSlashesAreCollapsed(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/pays/france/',
            amnesty_normalise_canonical_host('https://www.amnesty.fr//pays//france/')
        );
    }

    public function testParentSegmentIsResolved(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/pays/',
            amnesty_normalise_canonical_host('https://www.amnesty.fr/pays/france/../')
        );
    }

    public function testQueryStringAndTrailingSlashArePreserved(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/petitions/?theme=climat',
            amnesty_normalise_canonical_host('https://amnesty.preview.infomaniak.website/petitions/?theme=climat')
        );

        $this->assertSame(
            'https://www.amnesty.fr/petitions',
            amnesty_normalise_canonical_host('https://amnesty.preview.infomaniak.website/petitions')
        );
    }

    public function testCleanCanonicalIsLeftUnchanged(): void
    {
        $url = 'https://www.amnesty.fr/actualites/mon-article/';

        $this->assertSame($url, amnesty_normalise_canonical_host($url));
    }

    public function testEmptyCanonicalStaysEmpty(): void
    {
        $this->assertSame('', amnesty_normalise_canonical_host(''));
        $this->assertSame('', amnesty_normalise_canonical_host(null));
    }

    public function testRelativeCanonicalIsLeftUntouched(): void
    {
        $this->assertSame('/actualites/', amnesty_normalise_canonical_host('/actualites/'));
    }

    public function testHomepageCanonicalKeepsItsRootSlash(): void
    {
        $this->assertSame(
            'https://www.amnesty.fr/',
            amnesty_normalise_canonical_host('https://amnesty.preview.infomaniak.website/')
        );
    }

    public function testLocalPortIsPreserved(): void
    {
        $GLOBALS['__phpunit_home_url'] = 'http://localhost:8080';

        $this->assertSame(
            'http://localhost:8080/actualites/',
            amnesty_normalise_canonical_host('http://localhost:8080/./actualites/')
        );
    }

    /**
     * The regression behind the missing canonicals on /petitions/, /documents/,
     * /evenements/ and /reperes/: a post type archive has a queried object ID of
     * 0, which used to match an unset `amnesty_search_page` option.
     */
    public function testArchiveKeepsItsCanonicalWhenSearchPageIsUnset(): void
    {
        $GLOBALS['__phpunit_queried_object_id'] = 0;
        $GLOBALS['__phpunit_current_url']       = 'https://www.amnesty.fr/petitions/';

        $this->assertSame(
            'https://www.amnesty.fr/petitions/',
            amnesty_wpseo_canonical_filter('https://www.amnesty.fr/petitions/')
        );
    }

    public function testSearchPageWithoutFiltersKeepsItsCanonical(): void
    {
        $GLOBALS['__phpunit_options']['amnesty_search_page'] = 42;
        $GLOBALS['__phpunit_queried_object_id']              = 42;
        $GLOBALS['__phpunit_current_url']                    = 'https://www.amnesty.fr/recherche/';

        $this->assertSame(
            'https://www.amnesty.fr/recherche/',
            amnesty_wpseo_canonical_filter('https://www.amnesty.fr/recherche/')
        );
    }

    public function testFilteredSearchPageHasNoCanonical(): void
    {
        $GLOBALS['__phpunit_options']['amnesty_search_page'] = 42;
        $GLOBALS['__phpunit_queried_object_id']              = 42;
        $GLOBALS['__phpunit_current_url']                    = 'https://www.amnesty.fr/recherche/?q=torture';

        $this->assertSame('', amnesty_wpseo_canonical_filter('https://www.amnesty.fr/recherche/'));
    }

    public function testPagedSearchPageHasNoCanonical(): void
    {
        $GLOBALS['__phpunit_options']['amnesty_search_page'] = 42;
        $GLOBALS['__phpunit_queried_object_id']              = 42;
        $GLOBALS['__phpunit_is_paged']                       = true;

        $this->assertSame('', amnesty_wpseo_canonical_filter('https://www.amnesty.fr/recherche/'));
    }

    public function testSchemaGraphIsNormalisedRecursively(): void
    {
        $graph = [
            [
                '@id' => 'https://amnesty.preview.infomaniak.website/./actualites/#webpage',
                'url' => 'https://amnesty.preview.infomaniak.website/./actualites/',
                'name' => 'Actualités',
            ],
        ];

        $filtered = amnesty_filter_schema_graph($graph);

        $this->assertSame('https://www.amnesty.fr/actualites/#webpage', $filtered[0]['@id']);
        $this->assertSame('https://www.amnesty.fr/actualites/', $filtered[0]['url']);
        $this->assertSame('Actualités', $filtered[0]['name']);
    }

    public function testEmptyQueryStringYieldsAnEmptyArray(): void
    {
        $this->assertSame([], query_string_to_array(''));
        $this->assertSame([], query_string_to_array('&'));
    }

    public function testQueryStringIsParsedIntoPairs(): void
    {
        $this->assertSame([ 'a' => '1', 'b' => '2' ], query_string_to_array('a=1&b=2'));
        $this->assertSame([ 'a' => '' ], query_string_to_array('a'));
        $this->assertSame([ 'redirect' => 'https://www.amnesty.fr/?x=1' ], query_string_to_array('redirect=https://www.amnesty.fr/?x=1'));
    }
}
