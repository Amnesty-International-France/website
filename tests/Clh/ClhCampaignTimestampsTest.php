<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';

final class ClhCampaignTimestampsTest extends TestCase
{
    private const PAGE_ID = 2026;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_acf_field_values'] = [];
    }

    public function testParsesTheCampaignDates(): void
    {
        $this->seedDates('2026-10-01 08:00:00', '2026-11-15 23:59:00');

        self::assertSame(
            ['start' => strtotime('2026-10-01 08:00:00'), 'end' => strtotime('2026-11-15 23:59:00')],
            amnesty_get_clh_campaign_timestamps(self::PAGE_ID),
        );
    }

    #[DataProvider('unusableDates')]
    public function testUnusableDateIsZero(mixed $date): void
    {
        $this->seedDates($date, $date);

        self::assertSame(['start' => 0, 'end' => 0], amnesty_get_clh_campaign_timestamps(self::PAGE_ID));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function unusableDates(): iterable
    {
        yield 'empty' => [''];
        yield 'not a date' => ['not a date'];
    }

    private function seedDates(mixed $start, mixed $end): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::PAGE_ID] = [
            'start_date_highligth_clh' => $start,
            'end_date_highlight_clh' => $end,
        ];
    }
}
