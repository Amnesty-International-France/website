<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ClhPageTypeTrackingTest extends TestCase
{
    private const CAMPAIGN_PAGE_ID = 10;
    private const PETITION_ID = 95555;

    protected function setUp(): void
    {
        // Stubs live here, not at file level: each test runs in its own process,
        // so they never leak into the stand-ins of other test files.
        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return $GLOBALS['__phpunit_is_page'];
            }
            function is_singular(string $post_type = ''): bool
            {
                return $GLOBALS['__phpunit_singular_type'] === $post_type;
            }
            function get_query_var(string $key, mixed $default = ''): mixed
            {
                return $GLOBALS['__phpunit_query_vars'][$key] ?? $default;
            }
            function get_queried_object_id(): int
            {
                return $GLOBALS['__phpunit_queried_object_id'];
            }
            function wp_json_encode(mixed $value): string|false
            {
                return json_encode($value);
            }
            function amnesty_is_clh_petition_tunnel_page(): bool
            {
                return $GLOBALS['__phpunit_is_tunnel'];
            }
            function amnesty_is_clh_tunnel_final_page(): bool
            {
                return (bool) get_query_var('clh_final_thanks');
            }
            function amnesty_get_clh_petition_campaign_page(): ?object
            {
                return (object) ['ID' => 10];
            }
            function amnesty_is_petition_in_active_clh_campaign(int $petition_id): bool
            {
                return in_array($petition_id, $GLOBALS['__phpunit_clh_petition_ids'], true);
            }
            function amnesty_get_clh_tunnel_context(): array
            {
                return $GLOBALS['__phpunit_tunnel_context'];
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/theme-setup/analytics/clh-page-type.php';

        $GLOBALS['__phpunit_is_page'] = false;
        $GLOBALS['__phpunit_singular_type'] = '';
        $GLOBALS['__phpunit_query_vars'] = [];
        $GLOBALS['__phpunit_queried_object_id'] = 0;
        $GLOBALS['__phpunit_is_tunnel'] = false;
        $GLOBALS['__phpunit_clh_petition_ids'] = [self::PETITION_ID];
        $GLOBALS['__phpunit_tunnel_context'] = [];
    }

    public function testCampaignPageIsClhAccueil(): void
    {
        $this->onPage(self::CAMPAIGN_PAGE_ID);

        self::assertSame('clh_accueil', aif_get_clh_page_type());
        self::assertSame(['page_type' => 'clh_accueil'], $this->renderPush());
    }

    public function testOtherPageIsNotTracked(): void
    {
        $this->onPage(11);

        self::assertNull(aif_get_clh_page_type());
        self::assertSame('', $this->renderOutput());
    }

    public function testCampaignPetitionIsClhPetition(): void
    {
        $this->onPetition(self::PETITION_ID);

        self::assertSame(['page_type' => 'clh_petition'], $this->renderPush());
    }

    public function testPetitionOutsideTheCampaignIsNotTracked(): void
    {
        $this->onPetition(12);

        self::assertNull(aif_get_clh_page_type());
    }

    public function testCampaignPetitionThankYouPageIsNotTracked(): void
    {
        $this->onPetition(self::PETITION_ID);
        $GLOBALS['__phpunit_query_vars']['thanks'] = '1';

        self::assertNull(aif_get_clh_page_type());
    }

    public function testCampaignPetitionInMySpaceIsNotTracked(): void
    {
        $this->onPetition(self::PETITION_ID);
        $GLOBALS['__phpunit_query_vars']['is_my_space_petition'] = '1';

        self::assertNull(aif_get_clh_page_type());
    }

    public function testTunnelPushesTheStepOfTheFirstPetition(): void
    {
        $this->onTunnel([
            ['already_signed' => false, 'already_skipped' => false],
            ['already_signed' => false, 'already_skipped' => false],
        ]);

        self::assertSame(['page_type' => 'clh_tunnel', 'step' => 1], $this->renderPush());
    }

    public function testTunnelStepCountsSignedAndSkippedPetitions(): void
    {
        $this->onTunnel([
            ['already_signed' => true, 'already_skipped' => false],
            ['already_signed' => false, 'already_skipped' => true],
            ['already_signed' => false, 'already_skipped' => false],
        ]);

        self::assertSame(['page_type' => 'clh_tunnel', 'step' => 3], $this->renderPush());
    }

    public function testTunnelWithoutPetitionLeftPushesNoStep(): void
    {
        $this->onTunnel([
            ['already_signed' => true, 'already_skipped' => false],
        ], null);

        self::assertSame(['page_type' => 'clh_tunnel'], $this->renderPush());
    }

    public function testTunnelThankYouPageIsClhMerciWithoutStep(): void
    {
        $this->onTunnel([
            ['already_signed' => true, 'already_skipped' => false],
            ['already_signed' => false, 'already_skipped' => false],
        ]);
        $GLOBALS['__phpunit_query_vars']['clh_final_thanks'] = '1';

        self::assertSame(['page_type' => 'clh_merci'], $this->renderPush());
    }

    public function testPushIsHookedOnWpHead(): void
    {
        self::assertContains(
            'aif_output_clh_page_type_push',
            $GLOBALS['__phpunit_registered_actions']['wp_head'] ?? []
        );
    }

    private function onPage(int $page_id): void
    {
        $GLOBALS['__phpunit_is_page'] = true;
        $GLOBALS['__phpunit_queried_object_id'] = $page_id;
    }

    private function onPetition(int $petition_id): void
    {
        $GLOBALS['__phpunit_singular_type'] = 'petition';
        $GLOBALS['__phpunit_queried_object_id'] = $petition_id;
    }

    /**
     * @param array<array{already_signed: bool, already_skipped: bool}> $selected_posts
     */
    private function onTunnel(array $selected_posts, ?array $next_petition = ['id' => self::PETITION_ID]): void
    {
        $this->onPage(20);
        $GLOBALS['__phpunit_is_tunnel'] = true;
        $GLOBALS['__phpunit_tunnel_context'] = [
            'selected_posts' => $selected_posts,
            'next_petition' => $next_petition,
        ];
    }

    private function renderOutput(): string
    {
        ob_start();
        aif_output_clh_page_type_push();

        return (string) ob_get_clean();
    }

    /**
     * @return array<string, mixed>
     */
    private function renderPush(): array
    {
        $output = $this->renderOutput();

        self::assertStringContainsString('window.dataLayer = window.dataLayer || [];', $output);
        self::assertSame(1, preg_match('/window\.dataLayer\.push\((\{.*\})\);/', $output, $matches));

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
