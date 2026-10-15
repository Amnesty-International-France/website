<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhSignatureBandTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
        require_once __DIR__ . '/template-stand-ins.php';
        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => 2026];
        $GLOBALS['__phpunit_clh_tunnel_active'] = false;
        $GLOBALS['__phpunit_acf_field_values'] = [2026 => ['end_date_highlight_clh' => '2026-12-10 23:59:59']];
    }

    #[DataProvider('variants')]
    public function testRunningCampaignUsesBackendTargetAndPercentage(string $variant): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [(object) ['ID' => 1]];
        $GLOBALS['__phpunit_clh_signature_counts'] = [1 => 199999];
        // No target: use the backend default of 200,000 and floor 99.9995 to 99%.
        $html = $this->render($variant);
        self::assertStringContainsString('200 000', $html);
        self::assertStringContainsString('width: 99%;', $html);
        self::assertStringContainsString('aria-valuenow="99"', $html);
        self::assertStringContainsString('aria-valuemax="100"', $html);
        self::assertStringContainsString('Je signe', $html);
        self::assertStringContainsString('href="/changez-leur-histoire/campagne/"', $html);
        self::assertStringContainsString('sur un objectif de 200 000 signatures', $html);
        self::assertStringContainsString('99 %', $html);
    }

    #[DataProvider('variants')]
    public function testExceededTargetKeepsProgressWithinAriaRange(string $variant): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [(object) ['ID' => 1]];
        $GLOBALS['__phpunit_clh_signature_counts'] = [1 => 250000];
        $html = $this->render($variant);
        self::assertStringContainsString('250 000', $html);
        self::assertStringContainsString('aria-valuenow="100"', $html);
        self::assertStringContainsString('aria-valuemax="100"', $html);
        self::assertStringContainsString('100 %', $html);
        self::assertStringNotContainsString('Objectif atteint', $html);
    }

    #[DataProvider('variants')]
    public function testNoPetitionsKeepsANonzeroAriaRange(string $variant): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [];
        $html = $this->render($variant);
        self::assertStringContainsString('aria-valuenow="0"', $html);
        self::assertStringContainsString('aria-valuemax="100"', $html);
        self::assertStringContainsString('sur un objectif de 0 signatures', $html);
        self::assertStringContainsString('0 %', $html);
    }

    #[DataProvider('dayCounts')]
    public function testDayLabelAndRingUseTheProvidedFigures(int $days, string $label): void
    {
        ob_start();
        get_template_part('partials/clh-campaign-indicators', null, ['days' => $days, 'span' => 20]);
        $html = ob_get_clean();
        self::assertStringContainsString($label, $html);
        self::assertStringContainsString('--clh-ring: ' . ($days / 20), $html);
        self::assertStringNotContainsString('data-countdown', $html);
    }

    public static function dayCounts(): iterable
    {
        yield 'last day' => [1, '1 jour restant'];
        yield 'several days' => [10, '10 jours restants'];
    }

    #[DataProvider('variants')]
    public function testFinalResultsKeepZeroAndTheEnteredYear(string $variant): void
    {
        $GLOBALS['__phpunit_acf_field_values'][2026]['final_signatures_clh'] = '0';
        $GLOBALS['__phpunit_acf_field_values'][2026]['campaign_year_clh'] = '2024';
        $html = $this->render($variant);
        self::assertStringContainsString('0 signature recueillie en 2024', $html);
        self::assertStringNotContainsString('2026', $html);
        self::assertStringNotContainsString('progressbar', $html);
        self::assertStringNotContainsString('Je signe', $html);
        self::assertStringNotContainsString('clh-countdown', $html);
        if ('sticky' === $variant) {
            self::assertStringContainsString('data-clh-sticky-bar', $html);
        }
    }

    #[DataProvider('incompleteResults')]
    public function testIncompleteResultsProduceNoBand(string $variant, string $missing): void
    {
        $GLOBALS['__phpunit_acf_field_values'][2026]['final_signatures_clh'] = '1000';
        $GLOBALS['__phpunit_acf_field_values'][2026]['campaign_year_clh'] = '2024';
        unset($GLOBALS['__phpunit_acf_field_values'][2026][$missing]);
        self::assertSame('', trim($this->render($variant)));
    }

    public function testFinalSignatureFieldIsRegisteredOnlyOnce(): void
    {
        function acf_add_local_field_group(array $group): void
        {
            $GLOBALS['__phpunit_clh_field_group'] = $group;
        }
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/clh.php';
        foreach ($GLOBALS['__phpunit_registered_actions']['acf/include_fields'] as $callback) {
            $callback();
        }
        $fields = array_filter($GLOBALS['__phpunit_clh_field_group']['fields'], static fn ($field) => 'final_signatures_clh' === $field['name']);
        self::assertCount(1, $fields);
        self::assertSame('field_6a4d2c8e1b7f1', array_values($fields)[0]['key']);
    }

    public function testReminderIsVisibleWithoutJavaScriptOrRevealAnimation(): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $html = $this->render('reminder');
        self::assertStringContainsString('class="clh-band" data-clh-reminder', $html);
        self::assertStringNotContainsString('data-clh-sticky-bar', $html);
        self::assertStringNotContainsString('data-clh-reveal', $html);
        self::assertStringNotContainsString(' hidden', $html);
        self::assertStringContainsString('href="/changez-leur-histoire/campagne/"', $html);
    }

    public static function variants(): iterable
    {
        yield 'banner' => ['banner'];
        yield 'sticky' => ['sticky'];
        yield 'reminder' => ['reminder'];
    }

    public static function incompleteResults(): iterable
    {
        foreach (['banner', 'sticky', 'reminder'] as $variant) {
            foreach (['final_signatures_clh', 'campaign_year_clh'] as $missing) {
                yield "$variant without $missing" => [$variant, $missing];
            }
        }
    }

    private function render(string $variant): string
    {
        ob_start();
        get_template_part('partials/clh-signature-band', null, ['variant' => $variant]);
        return ob_get_clean();
    }
}
