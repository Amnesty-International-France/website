<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class FormSubmitTrackingTest extends TestCase
{
    private const CLH_PETITION_ID = 95555;

    protected function setUp(): void
    {
        // Stubs live here, not at file level: each test runs in its own process,
        // so they never leak into the stand-ins of other test files.
        if (!function_exists('wp_json_encode')) {
            function wp_json_encode(mixed $value): string|false
            {
                return json_encode($value);
            }
            function amnesty_is_petition_in_active_clh_campaign(int $petition_id): bool
            {
                return $petition_id === 95555;
            }
            function amnesty_get_clh_signed_petition_count_for_browser(): int
            {
                return 3;
            }
            function amnesty_get_clh_petition_campaign_page(): ?object
            {
                return (object) ['ID' => 2026];
            }
            function amnesty_get_clh_campaign_timestamps(int $page_id): array
            {
                return ['start' => $GLOBALS['__phpunit_clh_campaign_start'], 'end' => 0];
            }
        }

        $GLOBALS['__phpunit_clh_campaign_start'] = (int) strtotime('2026-10-15 00:00:00 UTC');

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/theme-setup/analytics/form-submit.php';
    }

    public function testRequestWithoutFormArgsPushesNothing(): void
    {
        self::assertNull(aif_get_form_submit_push([]));
        self::assertNull(aif_get_form_submit_push(['gtm_type' => 'petition']));
        self::assertSame('', $this->renderOutput([]));
    }

    public function testFormSubmitKeepsTypeAndName(): void
    {
        self::assertSame(
            ['event' => 'form_submit', 'type' => 'inscription', 'name' => 'newsLetter'],
            aif_get_form_submit_push(['gtm_type' => 'inscription', 'gtm_name' => 'newsLetter'])
        );
    }

    public function testClhSignatureCarriesTheCampaignData(): void
    {
        self::assertSame(
            [
                'event' => 'form_submit',
                'type' => 'petition',
                'name' => 'Liberté pour Marwa',
                'petition_id' => '95555',
                'campaign' => 'CLH_2026',
                'context' => 'tunnel',
                'signed_count' => 3,
            ],
            aif_get_form_submit_push($this->clhSignatureQuery('tunnel'))
        );
    }

    public function testCampaignFollowsTheStartYearOfTheCampaign(): void
    {
        $GLOBALS['__phpunit_clh_campaign_start'] = (int) strtotime('2027-10-15 00:00:00 UTC');

        self::assertSame('CLH_2027', aif_get_form_submit_push($this->clhSignatureQuery('tunnel'))['campaign']);
    }

    public function testCampaignWithoutStartDateIsLeftOut(): void
    {
        $GLOBALS['__phpunit_clh_campaign_start'] = 0;

        $push = aif_get_form_submit_push($this->clhSignatureQuery('tunnel'));

        self::assertArrayNotHasKey('campaign', $push);
        self::assertSame('95555', $push['petition_id']);
    }

    public function testClhSignatureFromThePetitionPage(): void
    {
        self::assertSame('fiche_petition', aif_get_form_submit_push($this->clhSignatureQuery('fiche_petition'))['context']);
    }

    public function testPetitionOutsideTheCampaignGetsNoCampaignData(): void
    {
        $query = ['gtm_petition_id' => '12'] + $this->clhSignatureQuery('tunnel');

        self::assertSame(
            ['event' => 'form_submit', 'type' => 'petition', 'name' => 'Liberté pour Marwa'],
            aif_get_form_submit_push($query)
        );
    }

    public function testUnknownContextGetsNoCampaignData(): void
    {
        self::assertArrayNotHasKey('campaign', aif_get_form_submit_push($this->clhSignatureQuery('header')));
    }

    public function testOutputPushesTheEventAndStripsTheArgsFromTheUrl(): void
    {
        $output = $this->renderOutput($this->clhSignatureQuery('tunnel'));

        self::assertStringContainsString('window.dataLayer = window.dataLayer || [];', $output);
        self::assertStringContainsString('"signed_count":3', $output);
        self::assertStringContainsString('["gtm_type","gtm_name","gtm_petition_id","gtm_context"].forEach', $output);
    }

    public function testTrackingArgsAreKeptForAFurtherRedirect(): void
    {
        self::assertSame(
            [
                'gtm_type' => 'petition',
                'gtm_name' => 'Libert%C3%A9%20pour%20Marwa',
                'gtm_petition_id' => '95555',
                'gtm_context' => 'tunnel',
            ],
            aif_get_form_submit_tracking_args(['clh_petition' => '12'] + $this->clhSignatureQuery('tunnel'))
        );
    }

    /**
     * @return array<string, string>
     */
    private function clhSignatureQuery(string $context): array
    {
        return [
            'gtm_type' => 'petition',
            'gtm_name' => 'Liberté pour Marwa',
            'gtm_petition_id' => (string) self::CLH_PETITION_ID,
            'gtm_context' => $context,
        ];
    }

    /**
     * @param array<string, string> $query
     */
    private function renderOutput(array $query): string
    {
        $_GET = $query;
        ob_start();
        aif_output_form_submit_push();

        return (string) ob_get_clean();
    }
}
