<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return $GLOBALS['__phpunit_is_admin'] ?? false;
    }
}

if (!function_exists('is_page')) {
    function is_page(int|string|array|null $page = null): bool
    {
        return $GLOBALS['__phpunit_is_page'] ?? false;
    }
}

if (!function_exists('is_preview')) {
    function is_preview(): bool
    {
        return $GLOBALS['__phpunit_is_preview'] ?? false;
    }
}

if (!function_exists('get_queried_object')) {
    function get_queried_object(): ?object
    {
        return $GLOBALS['__phpunit_queried_object'] ?? null;
    }
}

if (!function_exists('get_page_by_path')) {
    function get_page_by_path(string $path): ?object
    {
        return $GLOBALS['__phpunit_pages_by_path'][$path] ?? null;
    }
}

if (!function_exists('get_post_ancestors')) {
    function get_post_ancestors(int|object $post): array
    {
        $post_id = is_object($post) ? $post->ID : $post;

        return $GLOBALS['__phpunit_post_ancestors'][$post_id] ?? [];
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/theme-setup/analytics/my-space-login.php';

final class MySpaceLoginTrackingTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 42;
        $GLOBALS['__phpunit_is_admin'] = false;
        $GLOBALS['__phpunit_is_page'] = true;
        $GLOBALS['__phpunit_is_preview'] = false;
        $GLOBALS['__phpunit_pages_by_path'] = [
            'mon-espace' => (object) ['ID' => 10],
        ];
        $GLOBALS['__phpunit_queried_object'] = (object) ['ID' => 10];
        $GLOBALS['__phpunit_post_ancestors'] = [];
    }

    public function testOutputsTrackingOnMySpaceHomepageForLoggedInUser(): void
    {
        $output = $this->renderTrackingScript();

        self::assertTrue(aif_should_output_my_space_login_tracking());
        self::assertStringContainsString('aif_mon_espace_login_tracked', $output);
        self::assertStringContainsString("event: 'login'", $output);
        self::assertStringContainsString("method: 'mon_espace'", $output);
        self::assertStringContainsString("'max-age=1800'", $output);
        self::assertStringContainsString("'SameSite=Lax'", $output);
        self::assertStringContainsString("cookieAttributes.push('Secure')", $output);
        self::assertStringContainsString("document.cookie = cookieAttributes.join('; ')", $output);
        self::assertStringContainsString('if (cookieExists)', $output);
        self::assertStringContainsString('window.dataLayer = window.dataLayer || []', $output);
    }

    public function testOutputsTrackingOnMySpaceChildPageForLoggedInUser(): void
    {
        $GLOBALS['__phpunit_queried_object'] = (object) ['ID' => 20];
        $GLOBALS['__phpunit_post_ancestors'] = [
            20 => [10],
        ];

        $output = $this->renderTrackingScript();

        self::assertTrue(aif_should_output_my_space_login_tracking());
        self::assertStringContainsString("method: 'mon_espace'", $output);
    }

    public function testDoesNotOutputTrackingForLoggedOutUser(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 0;

        self::assertFalse(aif_should_output_my_space_login_tracking());
        self::assertSame('', $this->renderTrackingScript());
    }

    public function testDoesNotOutputTrackingOutsideMySpace(): void
    {
        $GLOBALS['__phpunit_queried_object'] = (object) ['ID' => 30];
        $GLOBALS['__phpunit_post_ancestors'] = [
            30 => [],
        ];

        self::assertFalse(aif_should_output_my_space_login_tracking());
        self::assertSame('', $this->renderTrackingScript());
    }

    public function testDoesNotOutputTrackingInAdmin(): void
    {
        $GLOBALS['__phpunit_is_admin'] = true;

        self::assertFalse(aif_should_output_my_space_login_tracking());
        self::assertSame('', $this->renderTrackingScript());
    }

    public function testDoesNotOutputTrackingOnPreview(): void
    {
        $GLOBALS['__phpunit_is_preview'] = true;

        self::assertFalse(aif_should_output_my_space_login_tracking());
        self::assertSame('', $this->renderTrackingScript());
    }

    private function renderTrackingScript(): string
    {
        ob_start();
        aif_output_my_space_login_tracking();

        return (string) ob_get_clean();
    }
}
