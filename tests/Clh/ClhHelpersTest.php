<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';

/**
 * Return shapes of the CLH page helpers, which the front-end relies on, and the
 * normalisation of the testimonials entered in the block.
 */
final class ClhHelpersTest extends TestCase
{
    private const GRID_PETITION_KEYS = ['id', 'title', 'short_title', 'image_id', 'signatures', 'target', 'percentage', 'url', 'sign_url'];

    private const LATEST_IMAGE_ID = 42;

    protected function setUp(): void
    {
        $_GET = [];
        $GLOBALS['__phpunit_get_posts_calls'] = [];
        $GLOBALS['__phpunit_get_posts_result'] = [self::LATEST_IMAGE_ID];
    }

    public function testPlaceholderImageIsTheLatestImageOfTheMediaLibrary(): void
    {
        self::assertSame(self::LATEST_IMAGE_ID, amnesty_get_clh_placeholder_image_id());

        $args = $GLOBALS['__phpunit_get_posts_calls'][0];
        self::assertSame('attachment', $args['post_type']);
        self::assertSame('image', $args['post_mime_type']);
        self::assertSame('ids', $args['fields']);
        self::assertSame(1, $args['numberposts']);
    }

    public function testPlaceholderImageIsZeroWhenTheMediaLibraryHasNoImage(): void
    {
        $GLOBALS['__phpunit_get_posts_result'] = [];

        self::assertSame(0, amnesty_get_clh_placeholder_image_id());
    }

    public function testGridPetitionsUseThePlaceholderImage(): void
    {
        self::assertSame([self::LATEST_IMAGE_ID], array_values(array_unique(array_column(amnesty_get_clh_grid_petitions(), 'image_id'))));
    }

    public function testScenarioIsDefaultWithoutParameter(): void
    {
        self::assertSame('default', amnesty_get_clh_scenario());
    }

    public function testScenarioReadsParameter(): void
    {
        $_GET['clh_scenario'] = 'no_petitions';

        self::assertSame('no_petitions', amnesty_get_clh_scenario());
    }

    public function testGridPetitionsShape(): void
    {
        $petitions = amnesty_get_clh_grid_petitions();

        self::assertCount(10, $petitions);
        self::assertTrue(array_is_list($petitions));
        self::assertNotContains(0, array_column($petitions, 'image_id'));

        foreach ($petitions as $petition) {
            self::assertSame(self::GRID_PETITION_KEYS, array_keys($petition));
            self::assertIsInt($petition['id']);
            self::assertIsInt($petition['image_id']);
            self::assertIsInt($petition['signatures']);
            self::assertIsInt($petition['target']);
            self::assertIsInt($petition['percentage']);
            self::assertIsString($petition['title']);
            self::assertIsString($petition['short_title']);
            self::assertIsString($petition['url']);
            self::assertIsString($petition['sign_url']);
        }
    }

    public function testGridPetitionsWithFewPetitions(): void
    {
        $_GET['clh_scenario'] = 'few_petitions';

        self::assertCount(6, amnesty_get_clh_grid_petitions());
    }

    public function testGridPetitionsWithoutPetitionsIsEmpty(): void
    {
        $_GET['clh_scenario'] = 'no_petitions';

        self::assertSame([], amnesty_get_clh_grid_petitions());
    }

    public function testGridPetitionsWithoutPhoto(): void
    {
        $_GET['clh_scenario'] = 'no_photo';

        self::assertContains(0, array_column(amnesty_get_clh_grid_petitions(), 'image_id'));
    }

    public function testTestimonialsAreGroupedByYearFromTheOldestToTheCurrentYear(): void
    {
        $current_year = (int) date('Y');

        $testimonials = amnesty_get_clh_testimonials(['testimonials' => [
            self::testimonial(2022, 'Personne A'),
            self::testimonial(2020, 'Personne B'),
        ]]);

        self::assertSame(range(2020, $current_year), array_keys($testimonials));
        self::assertSame(['Personne B'], array_column($testimonials[2020], 'name'));
        self::assertSame([], $testimonials[2021]);
        self::assertSame(['Personne A'], array_column($testimonials[2022], 'name'));
    }

    public function testTestimonialsKeepTheFirstCampaignYearAndTheCurrentYear(): void
    {
        $current_year = (int) date('Y');

        $testimonials = amnesty_get_clh_testimonials(['testimonials' => [
            self::testimonial($current_year, 'Personne A'),
            self::testimonial(2001, 'Personne B'),
        ]]);

        self::assertSame(range(2001, $current_year), array_keys($testimonials));
        self::assertSame(['Personne B'], array_column($testimonials[2001], 'name'));
        self::assertSame(['Personne A'], array_column($testimonials[$current_year], 'name'));
    }

    public function testTestimonialsOfTheSameYearKeepTheirEntryOrder(): void
    {
        $testimonials = amnesty_get_clh_testimonials(['testimonials' => [
            self::testimonial(2023, 'Personne B'),
            self::testimonial(2023, 'Personne A'),
        ]]);

        self::assertSame(['Personne B', 'Personne A'], array_column($testimonials[2023], 'name'));
    }

    /**
     * @param array<string, mixed> $entry
     * @param array<string, int|string> $expected
     */
    #[DataProvider('outputOfATestimonialCases')]
    public function testTestimonialOutput(array $entry, array $expected): void
    {
        $testimonials = amnesty_get_clh_testimonials(['testimonials' => [$entry]]);

        self::assertArrayHasKey(2023, $testimonials);
        self::assertSame([$expected], $testimonials[2023]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array<string, int|string>}>
     */
    public static function outputOfATestimonialCases(): iterable
    {
        yield 'complete testimonial' => [
            [
                'year' => 2023,
                'name' => 'Personne A',
                'country' => 'Pays A',
                'activity' => 'Journaliste',
                'quote' => 'Merci.',
                'description' => 'Libérée en 2023.',
                'imageId' => 7,
            ],
            [
                'name' => 'Personne A',
                'country' => 'Pays A',
                'activity' => 'Journaliste',
                'quote' => 'Merci.',
                'description' => 'Libérée en 2023.',
                'image_id' => 7,
            ],
        ];
        yield 'missing keys' => [
            ['year' => '2023'],
            [
                'name' => '',
                'country' => '',
                'activity' => '',
                'quote' => '',
                'description' => '',
                'image_id' => 0,
            ],
        ];
        yield 'non-scalar text' => [
            [...self::testimonial(2023, 'Personne A'), 'name' => ['Personne A']],
            [
                'name' => '',
                'country' => 'Pays',
                'activity' => 'Défenseure des droits humains',
                'quote' => 'Merci.',
                'description' => 'Libérée après une mobilisation internationale.',
                'image_id' => 7,
            ],
        ];
    }

    #[DataProvider('ignoredTestimonialCases')]
    public function testTestimonialIsIgnored(mixed $ignored): void
    {
        $testimonials = amnesty_get_clh_testimonials(['testimonials' => [
            $ignored,
            self::testimonial(2010, 'Personne A'),
        ]]);

        self::assertSame(range(2010, (int) date('Y')), array_keys($testimonials));
        self::assertSame(['Personne A'], array_column(array_merge(...array_values($testimonials)), 'name'));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function ignoredTestimonialCases(): iterable
    {
        yield 'year before the first campaign' => [self::testimonial(2000, 'Personne B')];
        yield 'year after the current year' => [self::testimonial((int) date('Y') + 1, 'Personne B')];
        yield 'year not a number' => [self::testimonial('abcd', 'Personne B')];
        yield 'year followed by letters' => [self::testimonial('2020abc', 'Personne B')];
        yield 'year with a leading space' => [self::testimonial(' 2020', 'Personne B')];
        yield 'year with decimals' => [self::testimonial('2020.5', 'Personne B')];
        yield 'missing year' => [['name' => 'Personne B']];
        yield 'not an array' => ['Personne B'];
    }

    #[DataProvider('noTestimonialCases')]
    public function testTestimonialsAreEmptyWithoutTestimonial(array $attributes): void
    {
        self::assertSame([], amnesty_get_clh_testimonials($attributes));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function noTestimonialCases(): iterable
    {
        yield 'missing attribute' => [[]];
        yield 'empty list' => [['testimonials' => []]];
        yield 'only ignored testimonials' => [['testimonials' => [self::testimonial(2000, 'Personne A')]]];
    }

    public function testDonationUrlPointsToTheDonationSiteWithAnOriginCode(): void
    {
        $url = amnesty_get_clh_donation_url();

        self::assertStringStartsWith('https://soutenir.amnesty.fr/', $url);
        self::assertStringContainsString('reserved_originecode=', $url);
    }

    /**
     * A testimonial as the block attribute stores it.
     *
     * @return array<string, int|string>
     */
    private static function testimonial(int|string $year, string $name): array
    {
        return [
            'year' => $year,
            'name' => $name,
            'country' => 'Pays',
            'activity' => 'Défenseure des droits humains',
            'quote' => 'Merci.',
            'description' => 'Libérée après une mobilisation internationale.',
            'imageId' => 7,
        ];
    }
}
