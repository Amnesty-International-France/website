<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The signature tunnel lives under the "Changez leur histoire" page at the root of the site
 * (CLH-49). The tunnel under the historical page in "Nos combats" is only the fallback, for
 * environments that still have the historical tree. Pointing at a missing page makes WordPress
 * guess a permalink from the 404 and send signers to an unrelated page.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelPageTest extends TestCase
{
    private const ROOT_PATH = 'changez-leur-histoire/campagne';
    private const HISTORICAL_PATH = 'nous-connaitre/nos-combats/changez-leur-histoire/campagne';

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_pages_by_path'] = [];
        $GLOBALS['__phpunit_page_lookups'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [];

        if (!function_exists('get_page_by_path')) {
            function get_page_by_path(string $path): ?WP_Post
            {
                $GLOBALS['__phpunit_page_lookups'][] = $path;

                return $GLOBALS['__phpunit_pages_by_path'][$path] ?? null;
            }
        }

        if (!function_exists('home_url')) {
            function home_url(string $path = ''): string
            {
                return 'https://www.amnesty.fr' . $path;
            }
        }

        if (!function_exists('trailingslashit')) {
            function trailingslashit(string $value): string
            {
                return rtrim($value, '/\\') . '/';
            }
        }

        if (!class_exists('WP_Post')) {
            eval('class WP_Post { public int $ID = 0; }');
        }

        if (!function_exists('is_admin')) {
            function is_admin(): bool
            {
                return false;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';
    }

    public function testTheRootTunnelWinsWhenBothPagesExist(): void
    {
        $root = $this->page(200, 'https://www.amnesty.fr/changez-leur-histoire/campagne/');
        $GLOBALS['__phpunit_pages_by_path'] = [
            self::ROOT_PATH => $root,
            self::HISTORICAL_PATH => $this->page(201, 'https://www.amnesty.fr/nous-connaitre/nos-combats/changez-leur-histoire/campagne/'),
        ];

        self::assertSame($root, amnesty_get_clh_petition_tunnel_page());
        self::assertSame('https://www.amnesty.fr/changez-leur-histoire/campagne/', amnesty_get_clh_petition_tunnel_url());
    }

    public function testTheHistoricalTunnelIsTheFallbackWithoutRootPage(): void
    {
        $historical = $this->page(201, 'https://www.amnesty.fr/nous-connaitre/nos-combats/changez-leur-histoire/campagne/');
        $GLOBALS['__phpunit_pages_by_path'] = [self::HISTORICAL_PATH => $historical];

        self::assertSame($historical, amnesty_get_clh_petition_tunnel_page());
        self::assertSame(
            'https://www.amnesty.fr/nous-connaitre/nos-combats/changez-leur-histoire/campagne/',
            amnesty_get_clh_petition_tunnel_url()
        );
    }

    public function testTheUrlFallsBackToTheRootPathWithoutEitherPage(): void
    {
        self::assertNull(amnesty_get_clh_petition_tunnel_page());
        self::assertSame('https://www.amnesty.fr/changez-leur-histoire/campagne/', amnesty_get_clh_petition_tunnel_url());
        self::assertSame(
            'https://www.amnesty.fr/changez-leur-histoire/campagne/merci/',
            amnesty_get_clh_petition_tunnel_thanks_url()
        );
    }

    public function testThePagesAreLookedUpOncePerRequest(): void
    {
        $GLOBALS['__phpunit_pages_by_path'] = [self::ROOT_PATH => $this->page(200, 'https://www.amnesty.fr/changez-leur-histoire/campagne/')];

        amnesty_get_clh_petition_tunnel_page();
        amnesty_get_clh_petition_tunnel_url();

        self::assertSame([self::ROOT_PATH], $GLOBALS['__phpunit_page_lookups']);
    }

    private function page(int $id, string $permalink): WP_Post
    {
        $page = new WP_Post();
        $page->ID = $id;
        $GLOBALS['__phpunit_post_permalinks'][$id] = $permalink;

        return $page;
    }
}
