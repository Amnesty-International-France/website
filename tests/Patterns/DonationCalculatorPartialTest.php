<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class DonationCalculatorPartialTest extends TestCase
{
    protected function setUp(): void
    {
        // Stubs live here, not at file level: each test runs in its own process,
        // so they never leak into the stand-ins of other test files.
        if (!function_exists('get_field')) {
            function get_field(string $selector, mixed $post_id = false): mixed
            {
                return null;
            }
        }
        if (!function_exists('get_template_directory')) {
            function get_template_directory(): string
            {
                return dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme';
            }
        }
        if (!function_exists('esc_attr')) {
            function esc_attr(mixed $text): string
            {
                return htmlspecialchars((string) $text, ENT_QUOTES);
            }
        }
        if (!function_exists('esc_url')) {
            function esc_url(string $url): string
            {
                return htmlspecialchars($url, ENT_QUOTES);
            }
        }
        if (!function_exists('locate_template')) {
            function locate_template(string $template_name): string
            {
                return dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $template_name;
            }
        }
    }

    #[DataProvider('rateCases')]
    public function testBlockRate(int|float|string $rate, string $expected): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/blocks/donation-calculator/render.php';
        $GLOBALS['post'] = (object) ['ID' => 1];

        $html = render_donation_calculator_block(['rate' => $rate, 'href' => 'https://soutenir.amnesty.fr/b?cid=395'], '', null);

        self::assertStringContainsString('data-rate="' . $expected . '"', $html);
    }

    /**
     * @return iterable<string, array{int|float|string, string}>
     */
    public static function rateCases(): iterable
    {
        yield 'unsupported rate falls back to 66' => [50, '66'];
        yield 'supported rate is kept' => [75, '75'];
        // WordPress validates a "number" attribute with is_numeric() and does not convert it.
        yield 'supported rate stored as a string is kept' => ['75', '75'];
        yield 'supported rate stored as a float is kept' => [75.0, '75'];
    }

    public function testPopinLinkKeepsTheHeaderPosition(): void
    {
        self::assertStringContainsString('data-position="header popin"', $this->render(['is_popin' => true]));
    }

    public function testContentLinkKeepsTheContentPosition(): void
    {
        self::assertStringContainsString('data-position="contenu"', $this->render([]));
    }

    public function testGivenPositionReplacesTheDefaultOne(): void
    {
        $html = $this->render(['is_popin' => true, 'position' => 'clh_merci']);

        self::assertStringContainsString('data-position="clh_merci"', $html);
        self::assertStringNotContainsString('header popin', $html);
    }

    public function testMonthlyTabIsOpenByDefault(): void
    {
        $html = $this->render(['with_tabs' => true]);

        self::assertStringContainsString('class="monthly active"', $html);
        self::assertStringContainsString('class="amount-monthly active"', $html);
        self::assertStringContainsString('class="punctual hidden"', $html);
        self::assertStringContainsString('class="amount-punctual hidden"', $html);
        self::assertStringContainsString('currency-punctual hidden-currency', $html);
        self::assertStringNotContainsString('currency-monthly hidden-currency', $html);
    }

    public function testPunctualTabIsOpenWhenAsked(): void
    {
        $html = $this->render(['with_tabs' => true, 'default_tab' => 'punctual']);

        self::assertStringContainsString('class="punctual active"', $html);
        self::assertStringContainsString('class="amount-punctual active"', $html);
        self::assertStringContainsString('class="monthly hidden"', $html);
        self::assertStringContainsString('class="amount-monthly hidden"', $html);
        self::assertStringContainsString('currency-monthly hidden-currency', $html);
        self::assertStringNotContainsString('currency-punctual hidden-currency', $html);
        // The preselected punctual amount does not change.
        self::assertMatchesRegularExpression('/id="radio-don-250"\s+checked/', $html);
    }

    public function testClhThankYouPageOpensOnThePunctualTab(): void
    {
        $pattern = (string) file_get_contents(dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/page-tunnel-clh-content.php');

        self::assertMatchesRegularExpression('/"position": "clh_merci", "default_tab": "punctual"/', $pattern);
    }

    /**
     * @param array<string, mixed> $args
     */
    private function render(array $args): string
    {
        $GLOBALS['post'] = (object) ['ID' => 1];
        $args += ['href' => 'https://soutenir.amnesty.fr/b?cid=395', 'rate' => 66];

        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/partials/donation-calculator.php';

        return (string) ob_get_clean();
    }
}
