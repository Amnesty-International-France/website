<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ClhSkipNonceFieldTest extends TestCase
{
    protected function setUp(): void
    {
        // Stub lives here, not at file level: other suites define a two-argument
        // wp_nonce_field() in the shared process, and this one records all four.
        if (!function_exists('wp_nonce_field')) {
            function wp_nonce_field(int|string $action = -1, string $name = '_wpnonce', bool $referer = true, bool $display = true): string
            {
                $GLOBALS['__phpunit_nonce_field_calls'][] = [$action, $name, $referer, $display];

                return '';
            }
        }

        $GLOBALS['__phpunit_nonce_field_calls'] = [];

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
    }

    public function testSkipNonceFieldLeavesOutTheReferer(): void
    {
        amnesty_clh_skip_nonce_field();

        self::assertSame(
            [['clh_skip_petition', 'clh_skip_nonce', false, true]],
            $GLOBALS['__phpunit_nonce_field_calls']
        );
    }
}
