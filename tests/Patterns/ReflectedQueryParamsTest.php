<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Query-string values echoed back by public and donor-space patterns must be
 * escaped, and status flags limited to the values the page itself produces.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ReflectedQueryParamsTest extends TestCase
{
    private const PAYLOAD = '"><script>alert(1)</script>';

    protected function setUp(): void
    {
        require_once __DIR__ . '/stubs.php';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $GLOBALS['__phpunit_get_posts_result'] = [(object) ['ID' => 1]];

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

    private function render(string $pattern, array $query): string
    {
        $_GET = $query;
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/' . $pattern . '.php';

        return (string) ob_get_clean();
    }

    public function testChronicleStatusRejectsUnknownValues(): void
    {
        $html = $this->render('page-discover-chronicle-content', ['inscription_chronique' => self::PAYLOAD]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('discover-chronicle-popin', $html);
        $this->assertStringContainsString('id="discover-chronicle-form"', $html);
    }

    public function testChronicleStatusKeepsKnownValues(): void
    {
        $success = $this->render('page-discover-chronicle-content', ['inscription_chronique' => 'success']);
        $already = $this->render('page-discover-chronicle-content', ['inscription_chronique' => 'already-sent']);

        $this->assertStringContainsString('class="discover-chronicle-popin success"', $success);
        $this->assertStringContainsString('class="discover-chronicle-popin already-sent"', $already);
    }

    public function testNewsletterStatusRejectsUnknownValues(): void
    {
        $html = $this->render('page-nl-content', ['inscription__nl' => self::PAYLOAD]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('newsletter-popin', $html);
        $this->assertStringContainsString('id="newsletter-form"', $html);
    }

    public function testNewsletterStatusKeepsSuccess(): void
    {
        $html = $this->render('page-nl-content', ['inscription__nl' => 'success']);

        $this->assertMatchesRegularExpression('/class="newsletter-popin\s+success"/', $html);
    }
}
