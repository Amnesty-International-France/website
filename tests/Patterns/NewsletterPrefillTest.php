<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The newsletter page prefills the address given in its URL. Displaying it
 * must not look the address up: the form would then tell whether it belongs
 * to a known signer or contact.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class NewsletterPrefillTest extends TestCase
{
    private const KNOWN = 'known@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/stubs.php';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $GLOBALS['__phpunit_get_posts_result'] = [(object) ['ID' => 1]];
        $GLOBALS['__phpunit_signer_lookups'] = [];
        $GLOBALS['__phpunit_local_users'] = [self::KNOWN => (object) ['id' => 7, 'email' => self::KNOWN]];

        // The newsletter pattern inlines a built SVG that is not versioned.
        $dir = sys_get_temp_dir() . '/aif-patterns-test';
        @mkdir($dir . '/assets/images', 0o777, true);
        file_put_contents($dir . '/assets/images/icon-letters.svg', '<svg></svg>');
        $GLOBALS['__phpunit_template_directory'] = $dir;
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testNewsletterPageDoesNotLookUpTheAddressFromTheUrl(): void
    {
        $_GET = ['email' => self::KNOWN];
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/page-nl-content.php';
        $html = (string) ob_get_clean();

        self::assertSame([], $GLOBALS['__phpunit_signer_lookups']);
        self::assertStringContainsString('id="lastname"', $html);
        self::assertStringContainsString('value="' . self::KNOWN . '"', $html);
    }
}
