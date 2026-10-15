<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';

/**
 * The CLH helpers still return hard-coded data: these tests pin their return
 * shapes, so the front-end can rely on them until the real logic lands.
 */
final class ClhHelpersTest extends TestCase
{
    private const TESTIMONIAL_KEYS = ['name', 'country', 'activity', 'quote', 'description', 'image_id'];

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

    public function testCampaignFiguresShape(): void
    {
        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame(
            ['days_remaining', 'seconds_remaining', 'span_days', 'signatures', 'target', 'percentage'],
            array_keys($figures),
        );
        self::assertContainsOnlyInt($figures);
        self::assertSame((int) min(100, floor($figures['signatures'] / $figures['target'] * 100)), $figures['percentage']);
    }

    public function testCampaignFiguresWithoutPetitionsAreZero(): void
    {
        $_GET['clh_scenario'] = 'no_petitions';

        $figures = amnesty_get_clh_campaign_figures();

        self::assertSame(0, $figures['signatures']);
        self::assertSame(0, $figures['target']);
        self::assertSame(0, $figures['percentage']);
    }

    public function testCampaignFiguresOnLastDay(): void
    {
        $_GET['clh_scenario'] = 'last_day';

        self::assertSame(1, amnesty_get_clh_campaign_figures()['days_remaining']);
    }

    public function testCampaignFiguresCapPercentageWhenGoalExceeded(): void
    {
        $_GET['clh_scenario'] = 'goal_exceeded';

        $figures = amnesty_get_clh_campaign_figures();

        self::assertGreaterThan($figures['target'], $figures['signatures']);
        self::assertSame(100, $figures['percentage']);
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

    public function testTestimonialsShape(): void
    {
        $testimonials = amnesty_get_clh_testimonials();

        self::assertNotEmpty($testimonials);

        $years = array_keys($testimonials);
        self::assertContainsOnlyInt($years);
        $sorted = $years;
        sort($sorted);
        self::assertSame($sorted, $years);

        foreach ($testimonials as $year_testimonials) {
            self::assertTrue(array_is_list($year_testimonials));

            foreach ($year_testimonials as $testimonial) {
                self::assertSame(self::TESTIMONIAL_KEYS, array_keys($testimonial));
                self::assertIsInt($testimonial['image_id']);
                self::assertNotSame(0, $testimonial['image_id']);
            }
        }
    }

    public function testTestimonialsHaveAYearWithSeveralTestimonials(): void
    {
        self::assertGreaterThan(1, max(array_map('count', amnesty_get_clh_testimonials())));
    }

    public function testTestimonialsWithoutTestimonialsIsEmpty(): void
    {
        $_GET['clh_scenario'] = 'no_testimonials';

        self::assertSame([], amnesty_get_clh_testimonials());
    }

    public function testTestimonialsWithSingleTestimonial(): void
    {
        $_GET['clh_scenario'] = 'single_testimonial';

        $testimonials = amnesty_get_clh_testimonials();

        self::assertCount(1, $testimonials);
        self::assertCount(1, reset($testimonials));
    }

    public function testTestimonialsWithoutPhoto(): void
    {
        $_GET['clh_scenario'] = 'no_photo';

        $image_ids = array_column(array_merge(...array_values(amnesty_get_clh_testimonials())), 'image_id');

        self::assertContains(0, $image_ids);
    }

    public function testDonationUrlPointsToTheDonationSiteWithAnOriginCode(): void
    {
        $url = amnesty_get_clh_donation_url();

        self::assertStringStartsWith('https://soutenir.amnesty.fr/', $url);
        self::assertStringContainsString('reserved_originecode=', $url);
    }
}
