<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ClhCampaignEdgeCacheTest extends TestCase
{
    private const CAMPAIGN_PAGE_ID = 10;

    protected function setUp(): void
    {
        // Stubs live here, not at file level: each test runs in its own process,
        // so they never leak into the stand-ins of other test files.
        if (!function_exists('is_page')) {
            function is_page(mixed $page = ''): bool
            {
                return is_int($page) && $page === $GLOBALS['__phpunit_queried_page_id'];
            }
            function is_preview(): bool
            {
                return $GLOBALS['__phpunit_is_preview'];
            }
            function is_search(): bool
            {
                return false;
            }
            function is_404(): bool
            {
                return false;
            }
            function is_feed(): bool
            {
                return false;
            }
            function amnesty_is_clh_petition_tunnel_page(): bool
            {
                return $GLOBALS['__phpunit_is_tunnel'];
            }
        }

        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/root/caching.php';

        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => self::CAMPAIGN_PAGE_ID];
        $GLOBALS['__phpunit_queried_page_id'] = self::CAMPAIGN_PAGE_ID;
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_is_preview'] = false;
        $GLOBALS['__phpunit_is_tunnel'] = false;
    }

    public function testAnonymousCampaignPageIsCachedOneMinuteAtTheEdge(): void
    {
        self::assertSame('max-age=60', amnesty_cloudflare_cdn_cache_control());
    }

    /**
     * @param array<string, mixed> $state globals overriding the anonymous campaign page request
     */
    #[DataProvider('requestsWithoutEdgeTtl')]
    public function testNoEdgeTtl(array $state): void
    {
        foreach ($state as $name => $value) {
            $GLOBALS[$name] = $value;
        }

        self::assertNull(amnesty_cloudflare_cdn_cache_control());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function requestsWithoutEdgeTtl(): iterable
    {
        yield 'logged-in user' => [['__phpunit_current_user_id' => 7]];
        yield 'another page' => [['__phpunit_queried_page_id' => 11]];
        yield 'no campaign page' => [['__phpunit_clh_campaign_page' => null]];
        yield 'campaign tunnel' => [['__phpunit_is_tunnel' => true]];
        yield 'preview' => [['__phpunit_is_preview' => true]];
    }

    public function testHeaderIsSentOnTemplateRedirect(): void
    {
        self::assertArrayHasKey('template_redirect', $GLOBALS['__phpunit_registered_actions']);
        self::assertContains(
            'amnesty_send_cloudflare_cdn_cache_control',
            $GLOBALS['__phpunit_registered_actions']['template_redirect'],
        );
    }
}
