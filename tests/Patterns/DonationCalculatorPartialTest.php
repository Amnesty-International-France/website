<?php

declare(strict_types=1);

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
