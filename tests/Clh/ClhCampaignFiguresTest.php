<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
require_once __DIR__ . '/petitions-stand-ins.php';

final class ClhCampaignFiguresTest extends TestCase
{
    private const PAGE_ID = 2026;

    private const DAY = 86_400;

    private const HOUR = 3_600;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => self::PAGE_ID];
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [];
        $GLOBALS['__phpunit_clh_signature_counts'] = [];
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $this->seedDates(time() - 10 * self::DAY, time() + 5 * self::DAY + self::HOUR);
    }

    public function testShape(): void
    {
        $this->seedPetition(1, 1500, 2000);

        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame(
            ['days_remaining', 'seconds_remaining', 'span_days', 'signatures', 'target', 'percentage'],
            array_keys($figures),
        );
        self::assertContainsOnlyInt($figures);
    }

    /**
     * @param list<array{int, int|string}> $petitions signatures and target of each petition
     */
    #[DataProvider('petitionTotals')]
    public function testTotalsOfThePetitions(array $petitions, int $signatures, int $target, int $percentage): void
    {
        foreach ($petitions as $index => [$petition_signatures, $petition_target]) {
            $this->seedPetition($index + 1, $petition_signatures, $petition_target);
        }

        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame($signatures, $figures['signatures']);
        self::assertSame($target, $figures['target']);
        self::assertSame($percentage, $figures['percentage']);
    }

    /**
     * @return iterable<string, array{petitions: list<array{int, int|string}>, signatures: int, target: int, percentage: int}>
     */
    public static function petitionTotals(): iterable
    {
        yield 'summed, percentage rounded down' => [
            'petitions' => [[1_000, 1_500], [999, 1_500]],
            'signatures' => 1_999,
            'target' => 3_000,
            'percentage' => 66,
        ];
        yield 'whole percentage kept exact' => [
            'petitions' => [[58_000, 200_000]],
            'signatures' => 58_000,
            'target' => 200_000,
            'percentage' => 29,
        ];
        yield 'empty target counts the default target' => [
            'petitions' => [[1_000, 3_000], [0, '']],
            'signatures' => 1_000,
            'target' => 203_000,
            'percentage' => 0,
        ];
        yield 'zero target counts the default target' => [
            'petitions' => [[1_000, 3_000], [0, 0]],
            'signatures' => 1_000,
            'target' => 203_000,
            'percentage' => 0,
        ];
        yield 'percentage capped when the target is exceeded' => [
            'petitions' => [[2_500, 2_000]],
            'signatures' => 2_500,
            'target' => 2_000,
            'percentage' => 100,
        ];
        yield 'no petition' => [
            'petitions' => [],
            'signatures' => 0,
            'target' => 0,
            'percentage' => 0,
        ];
    }

    #[DataProvider('remainingTimes')]
    public function testDaysRemainingCountsTheCurrentDay(int $seconds_left, int $expected_days): void
    {
        $this->seedDates(time() - 10 * self::DAY, time() + $seconds_left);

        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame($expected_days, $figures['days_remaining']);
        self::assertEqualsWithDelta($seconds_left, $figures['seconds_remaining'], 2);
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function remainingTimes(): iterable
    {
        yield '2 days 1 hour' => [2 * self::DAY + self::HOUR, 3];
        yield 'last day' => [3 * self::HOUR, 1];
    }

    public function testRemainingTimeIsZeroWhenTheCampaignIsNotInProgress(): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = false;
        $this->seedPetition(1, 500, 1_000);

        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame(0, $figures['days_remaining']);
        self::assertSame(0, $figures['seconds_remaining']);
        self::assertSame(500, $figures['signatures']);
        self::assertSame(1_000, $figures['target']);
        self::assertSame(50, $figures['percentage']);
    }

    #[DataProvider('campaignSpans')]
    public function testSpanDaysIsTheCampaignLength(?int $start_offset, ?int $end_offset, int $expected_days): void
    {
        $now = time();
        $this->seedDates(
            $start_offset === null ? null : $now + $start_offset,
            $end_offset === null ? null : $now + $end_offset,
        );

        self::assertSame($expected_days, amnesty_get_clh_campaign_figures()['span_days']);
    }

    /**
     * @return iterable<string, array{?int, ?int, int}>
     */
    public static function campaignSpans(): iterable
    {
        yield '45 days' => [-40 * self::DAY, 5 * self::DAY, 45];
        yield 'partial day counted' => [-self::DAY, self::HOUR, 2];
        yield 'start missing' => [null, 5 * self::DAY, 0];
        yield 'end missing' => [-40 * self::DAY, null, 0];
        yield 'end before start' => [5 * self::DAY, -40 * self::DAY, 0];
    }

    public function testFiguresAreZeroWithoutCampaignPage(): void
    {
        $GLOBALS['__phpunit_clh_campaign_page'] = null;
        $this->seedPetition(1, 500, 1_000);

        self::assertSame(
            ['days_remaining' => 0, 'seconds_remaining' => 0, 'span_days' => 0, 'signatures' => 0, 'target' => 0, 'percentage' => 0],
            amnesty_get_clh_campaign_figures(),
        );
    }

    private function seedDates(?int $start, ?int $end): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::PAGE_ID] = [
            'start_date_highligth_clh' => $start === null ? '' : date('Y-m-d H:i:s', $start),
            'end_date_highlight_clh' => $end === null ? '' : date('Y-m-d H:i:s', $end),
        ];
    }

    private function seedPetition(int $id, int $signatures, int|string $target): void
    {
        $GLOBALS['__phpunit_clh_campaign_petitions'][] = (object) ['ID' => $id];
        $GLOBALS['__phpunit_clh_signature_counts'][$id] = $signatures;
        $GLOBALS['__phpunit_acf_field_values'][$id] = ['objectif_signatures' => $target];
    }
}
