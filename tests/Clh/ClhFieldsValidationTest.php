<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/fields.php';

final class ClhFieldsValidationTest extends TestCase
{
    private const EARLIER_ERROR = 'Value is required';

    private const START_DATE = '2026-11-01 00:00:00';

    protected function setUp(): void
    {
        $_POST = [];
    }

    #[DataProvider('acceptedEndDates')]
    public function testAcceptsEndDate(string $end_date): void
    {
        $this->postStartDate(self::START_DATE);

        self::assertTrue(self::validate('end_date_highlight_clh', $end_date));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function acceptedEndDates(): iterable
    {
        yield 'equal to the start date' => [self::START_DATE];
        yield 'after the start date' => ['2026-12-10 23:59:59'];
        yield 'unreadable, left to ACF' => ['not a date'];
    }

    public function testRejectsEndDateBeforeStartDate(): void
    {
        $this->postStartDate(self::START_DATE);

        $this->assertIsError(self::validate('end_date_highlight_clh', '2026-10-31 23:59:59'));
    }

    public function testAcceptsEndDateWhenStartDateIsNotPosted(): void
    {
        self::assertTrue(self::validate('end_date_highlight_clh', '2026-10-31 23:59:59'));
    }

    #[DataProvider('acceptedNumbers')]
    public function testAcceptsNumber(string $name, mixed $value): void
    {
        self::assertTrue(self::validate($name, $value));
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function acceptedNumbers(): iterable
    {
        foreach (['final_signatures_clh', 'campaign_year_clh'] as $name) {
            yield "{$name}: empty" => [$name, ''];
            yield "{$name}: null" => [$name, null];
        }

        yield 'final_signatures_clh: zero' => ['final_signatures_clh', '0'];
        yield 'final_signatures_clh: the cap' => ['final_signatures_clh', '99999999'];
        yield 'campaign_year_clh: the first campaign' => ['campaign_year_clh', '2001'];
        yield 'campaign_year_clh: the current year' => ['campaign_year_clh', date('Y')];
    }

    #[DataProvider('rejectedNumbers')]
    public function testRejectsNumber(string $name, mixed $value): void
    {
        $this->assertIsError(self::validate($name, $value));
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function rejectedNumbers(): iterable
    {
        foreach (['final_signatures_clh', 'campaign_year_clh'] as $name) {
            yield "{$name}: decimal" => [$name, '2024.5'];
            yield "{$name}: thousands separator" => [$name, '2 024'];
            yield "{$name}: negative" => [$name, '-1'];
            yield "{$name}: list" => [$name, ['2024']];
        }

        yield 'final_signatures_clh: above the cap' => ['final_signatures_clh', '100000000'];
        yield 'campaign_year_clh: before the first campaign' => ['campaign_year_clh', '2000'];
        yield 'campaign_year_clh: a future year' => ['campaign_year_clh', (string) ((int) date('Y') + 1)];
    }

    #[DataProvider('valuesAfterAnEarlierError')]
    public function testKeepsAnEarlierError(string $name, string $value): void
    {
        $this->postStartDate(self::START_DATE);

        self::assertSame(self::EARLIER_ERROR, self::validate($name, $value, self::EARLIER_ERROR));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valuesAfterAnEarlierError(): iterable
    {
        yield 'end date before the start date' => ['end_date_highlight_clh', '2026-10-31 23:59:59'];
        yield 'final signatures with a separator' => ['final_signatures_clh', '1 000'];
        yield 'campaign year before the first campaign' => ['campaign_year_clh', '2000'];
    }

    /**
     * Mimics ACF validating one posted value: it goes through the
     * acf/validate_value/name={name} filters, after ACF's own field-type checks.
     */
    private static function validate(string $name, mixed $value, bool|string $valid = true): bool|string
    {
        $callbacks = $GLOBALS['__phpunit_registered_filters']["acf/validate_value/name={$name}"] ?? [];
        self::assertNotEmpty($callbacks, "No acf/validate_value filter registered for {$name}.");

        foreach ($callbacks as $callback) {
            $valid = $callback($valid, $value, ['name' => $name], "acf[{$name}]");
        }

        return $valid;
    }

    private function postStartDate(string $start_date): void
    {
        $_POST['acf'][AMNESTY_CLH_START_DATE_FIELD_KEY] = $start_date;
    }

    private function assertIsError(bool|string $valid): void
    {
        self::assertIsString($valid);
        self::assertNotSame('', $valid);
    }
}
