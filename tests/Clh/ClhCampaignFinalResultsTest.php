<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';

if (!function_exists('amnesty_get_clh_petition_campaign_page')) {
    /**
     * Stand-in for the lookup of includes/post-types/petitions.php, which caches its result for
     * the whole run: returns the page seeded in $GLOBALS['__phpunit_clh_campaign_page'].
     */
    function amnesty_get_clh_petition_campaign_page(): ?object
    {
        return $GLOBALS['__phpunit_clh_campaign_page'] ?? null;
    }
}

final class ClhCampaignFinalResultsTest extends TestCase
{
    private const PAGE_ID = 2026;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => self::PAGE_ID];
        $GLOBALS['__phpunit_acf_field_values'] = [];
    }

    /**
     * @param array{signatures: int, year: int} $expected
     */
    #[DataProvider('filledResults')]
    public function testReturnsSignaturesAndYear(int|string $signatures, int|string $year, array $expected): void
    {
        $this->seedFinalResults($signatures, $year);

        self::assertSame($expected, amnesty_get_clh_campaign_final_results());
    }

    /**
     * @return iterable<string, array{int|string, int|string, array{signatures: int, year: int}}>
     */
    public static function filledResults(): iterable
    {
        yield 'integers' => [1_243_567, 2026, ['signatures' => 1_243_567, 'year' => 2026]];
        yield 'stored strings' => ['1500', '2025', ['signatures' => 1500, 'year' => 2025]];
        yield 'zero signatures' => ['0', '2024', ['signatures' => 0, 'year' => 2024]];
    }

    #[DataProvider('incompleteResults')]
    public function testIsNull(int|string|null $signatures, int|string|null $year): void
    {
        $this->seedFinalResults($signatures, $year);

        self::assertNull(amnesty_get_clh_campaign_final_results());
    }

    /**
     * @return iterable<string, array{int|string|null, int|string|null}>
     */
    public static function incompleteResults(): iterable
    {
        yield 'signatures missing' => ['', '2026'];
        yield 'year missing' => ['1500', null];
        yield 'both missing' => [null, null];
        yield 'not a whole number' => ['1500.5', '2026'];
        yield 'negative' => [-5, 2026];
        yield 'trailing newline' => ["1500\n", '2026'];
    }

    public function testIsNullWithoutCampaignPage(): void
    {
        $GLOBALS['__phpunit_clh_campaign_page'] = null;
        $this->seedFinalResults(1500, 2026);

        self::assertNull(amnesty_get_clh_campaign_final_results());
    }

    private function seedFinalResults(int|string|null $signatures, int|string|null $year): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::PAGE_ID] = [
            'final_signatures_clh' => $signatures,
            'campaign_year_clh' => $year,
        ];
    }
}
