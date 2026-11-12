<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class PostSharePatternTest extends TestCase
{
    protected function setUp(): void
    {
        // Stubs live here, not at file level: each test runs in its own process,
        // so they never leak into the stand-ins of other test files.
        if (!function_exists('get_the_ID')) {
            function get_the_ID(): int
            {
                return 10;
            }
            function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
            {
                return '';
            }
            function amnesty_validate_boolish(mixed $value): bool
            {
                return (bool) $value;
            }
            function spaceless(): void
            {
            }
            function endspaceless(): void
            {
            }
            function get_the_title(): string
            {
                return 'Changez leur histoire';
            }
            function esc_attr_e(string $text, string $domain = 'default'): void
            {
                echo htmlspecialchars($text, ENT_QUOTES);
            }
            function esc_url(string $url): string
            {
                return htmlspecialchars($url, ENT_QUOTES);
            }
            function get_template_directory(): string
            {
                return dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme';
            }
            function aif_get_clh_page_type(): ?string
            {
                return $GLOBALS['__phpunit_clh_page_type'];
            }
        }
    }

    public function testClhPageShareButtonsCarryTheTagPlanAttributes(): void
    {
        $GLOBALS['__phpunit_clh_page_type'] = 'clh_accueil';
        $html = $this->render();

        foreach (['facebook', 'bluesky', 'mastodon', 'copier le lien', 'email'] as $network) {
            self::assertStringContainsString('data-gtm="clic_partage" data-name="' . $network . '"', $html);
        }
    }

    public function testOtherPagesKeepUntrackedShareButtons(): void
    {
        $GLOBALS['__phpunit_clh_page_type'] = null;

        self::assertStringNotContainsString('data-gtm', $this->render());
    }

    private function render(): string
    {
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/post-share.php';

        return (string) ob_get_clean();
    }
}
