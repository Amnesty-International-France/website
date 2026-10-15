<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhPetitionsGridTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';

        function get_the_ID(): int
        {
            return 2026;
        }

        function amnesty_get_active_clh_campaign_for_page(int $page_id): ?object
        {
            return amnesty_is_clh_petition_tunnel_active() ? amnesty_get_clh_petition_campaign_page() : null;
        }

        function esc_html(string $text): string
        {
            return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        function number_format_i18n(int $number): string
        {
            return number_format($number, 0, ',', ' ');
        }

        // The card is independent of the grid's campaign-year heading.
        function get_template_part(string $slug, ?string $name = null, array $args = []): void
        {
        }

        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => 2026];
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [(object) ['ID' => 1]];
        $GLOBALS['__phpunit_acf_field_values'] = [2026 => [
            'end_date_highlight_clh' => '2027-01-10 23:59:59',
            'final_signatures_clh' => '0',
            'campaign_year_clh' => '2026',
        ]];
    }

    #[DataProvider('campaigns')]
    public function testHeadingUsesFinalYearOnlyForACompletedCampaign(bool $active, bool $complete_results, string $heading): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = $active;
        if (!$complete_results) {
            unset($GLOBALS['__phpunit_acf_field_values'][2026]['final_signatures_clh']);
        }

        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/clh-petitions-grid.php';
        $html = ob_get_clean();
        self::assertStringContainsString($heading, $html);
    }

    public static function campaigns(): iterable
    {
        yield 'closed campaign extended into January' => [false, true, 'Les 1 situations de la campagne 2026'];
        yield 'closed without complete results' => [false, false, 'Les 1 situations de la campagne 2027'];
        yield 'running campaign' => [true, true, 'Les situations que nous défendons en 2027'];
    }
}
