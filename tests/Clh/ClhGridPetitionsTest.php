<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
require_once __DIR__ . '/petitions-stand-ins.php';

/**
 * Petitions of the campaign grid: selection, order and values of each card.
 */
final class ClhGridPetitionsTest extends TestCase
{
    private const TUNNEL_URL = 'https://example.org/changez-leur-histoire/campagne/';

    private const DAY = 86_400;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [];
        $GLOBALS['__phpunit_clh_signature_counts'] = [];
        $GLOBALS['__phpunit_clh_tunnel_url'] = self::TUNNEL_URL;
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [];
        $GLOBALS['__phpunit_post_thumbnail_ids'] = [];
    }

    public function testValuesOfAPetition(): void
    {
        $this->seedPetition(7, '31.12.2026', ['short_title' => 'Personne A', 'objectif_signatures' => '2000'], 1_500);
        $GLOBALS['__phpunit_post_thumbnail_ids'][7] = 42;

        self::assertSame([[
            'id' => 7,
            'title' => 'Personne A',
            'image_id' => 42,
            'signatures' => 1_500,
            'target' => 2_000,
            'percentage' => 75,
            'url' => 'https://example.org/petitions/7/',
            'sign_url' => self::TUNNEL_URL . '?clh_petition=7',
        ]], amnesty_get_clh_grid_petitions());
    }

    #[DataProvider('blankClhTitleCases')]
    public function testClhTitleFallsBackToThePostTitle(string $clh_title): void
    {
        $this->seedPetition(1, '01.11.2026', ['short_title' => $clh_title]);

        $petitions = amnesty_get_clh_grid_petitions();

        self::assertArrayHasKey(0, $petitions);
        self::assertSame('Pétition 1', $petitions[0]['title']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blankClhTitleCases(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces only' => ['  '];
    }

    public function testImageIdIsZeroWithoutPhoto(): void
    {
        $this->seedPetition(1, self::daysFromNow(5));
        $this->seedPetition(2, self::daysFromNow(6));
        $GLOBALS['__phpunit_post_thumbnail_ids'][1] = 42;

        self::assertSame([42, 0], array_column(amnesty_get_clh_grid_petitions(), 'image_id'));
    }

    public function testUrlIsEmptyWithoutPermalink(): void
    {
        $this->seedPetition(1);
        unset($GLOBALS['__phpunit_post_permalinks'][1]);

        $petitions = amnesty_get_clh_grid_petitions();

        self::assertArrayHasKey(0, $petitions);
        self::assertSame('', $petitions[0]['url']);
    }

    #[DataProvider('progressCases')]
    public function testProgressOfAPetition(int $signatures, string $target_field, int $target, int $percentage): void
    {
        $this->seedPetition(1, '01.11.2026', ['objectif_signatures' => $target_field], $signatures);

        $petitions = amnesty_get_clh_grid_petitions();

        self::assertArrayHasKey(0, $petitions);
        self::assertSame($target, $petitions[0]['target']);
        self::assertSame($percentage, $petitions[0]['percentage']);
    }

    /**
     * @return iterable<string, array{int, string, int, int}>
     */
    public static function progressCases(): iterable
    {
        yield 'percentage rounded down' => [1_999, '3000', 3_000, 66];
        yield 'empty target counts the default target' => [50_000, '', 200_000, 25];
        yield 'percentage capped at 100' => [3_000, '2000', 2_000, 100];
    }

    public function testActivePetitionsComeFirstByClosestEndDateThenClosedOnesByMostRecent(): void
    {
        $this->seedPetition(1, self::daysFromNow(30));
        $this->seedPetition(2, self::daysFromNow(-400));
        $this->seedPetition(3, self::daysFromNow(5));
        $this->seedPetition(4, self::daysFromNow(-10));

        self::assertSame([3, 1, 4, 2], array_column(amnesty_get_clh_grid_petitions(), 'id'));
    }

    public function testPetitionEndingTodayIsClosed(): void
    {
        $this->seedPetition(1, self::daysFromNow(0));
        $this->seedPetition(2, self::daysFromNow(1));

        self::assertSame([2, 1], array_column(amnesty_get_clh_grid_petitions(), 'id'));
    }

    public function testPetitionsWithoutValidEndDateComeLastAndTiesKeepTheQueryOrder(): void
    {
        $this->seedPetition(1, '');
        $this->seedPetition(4, self::daysFromNow(5));
        $this->seedPetition(3, 'not a date');
        $this->seedPetition(2, self::daysFromNow(5));
        $this->seedPetition(5, self::daysFromNow(-3));
        $this->seedPetition(6, '31.02.2026');
        $this->seedPetition(7, self::daysFromNow(-3));

        self::assertSame([4, 2, 5, 7, 1, 3, 6], array_column(amnesty_get_clh_grid_petitions(), 'id'));
    }

    public function testAtMostTenPetitionsClosedOnesDroppedFirst(): void
    {
        $this->seedPetition(1, self::daysFromNow(-1));
        $this->seedPetition(2, self::daysFromNow(-2));

        for ($id = 3; $id <= 12; $id++) {
            $this->seedPetition($id, self::daysFromNow(15 - $id));
        }

        self::assertSame(range(12, 3), array_column(amnesty_get_clh_grid_petitions(), 'id'));
    }

    public function testEmptyWithoutPetitions(): void
    {
        self::assertSame([], amnesty_get_clh_grid_petitions());
    }

    /**
     * Adds a petition to the campaign, after the ones already seeded (query order).
     *
     * @param array<string, string> $fields ACF values of the petition, besides its end date
     */
    private function seedPetition(int $id, string $end_date = '01.11.2026', array $fields = [], int $signatures = 0): void
    {
        $GLOBALS['__phpunit_clh_campaign_petitions'][] = (object) ['ID' => $id, 'post_title' => sprintf('Pétition %d', $id)];
        $GLOBALS['__phpunit_acf_field_values'][$id] = ['date_de_fin' => $end_date, ...$fields];
        $GLOBALS['__phpunit_clh_signature_counts'][$id] = $signatures;
        $GLOBALS['__phpunit_post_permalinks'][$id] = sprintf('https://example.org/petitions/%d/', $id);
    }

    /**
     * An end date as ACF returns it (d.m.Y), some days from today; negative for a closed petition.
     */
    private static function daysFromNow(int $days): string
    {
        return date('d.m.Y', time() + $days * self::DAY);
    }
}
