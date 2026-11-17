<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class GtmContainerTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/theme-setup/analytics/gtm-container.php';
    }

    public function testContainerIsHookedOnTheHeadAndTheBodyOpening(): void
    {
        self::assertContains(['aif_output_gtm_container_script', 1], $GLOBALS['__phpunit_registered_action_priorities']['wp_head'] ?? []);
        self::assertContains(['aif_output_gtm_container_noscript', 1], $GLOBALS['__phpunit_registered_action_priorities']['wp_body_open'] ?? []);
    }

    public function testScriptLoadsTheContainerOutsideRocketLoader(): void
    {
        ob_start();
        aif_output_gtm_container_script();
        $output = (string) ob_get_clean();

        // Cloudflare Rocket Loader defers any inline script without this attribute.
        self::assertStringContainsString('<script data-cfasync="false">', $output);
        self::assertStringContainsString("'dataLayer', 'GTM-MBQ25R'", $output);
    }

    public function testNoscriptLoadsTheContainerIframe(): void
    {
        ob_start();
        aif_output_gtm_container_noscript();
        $output = (string) ob_get_clean();

        self::assertStringContainsString('https://www.googletagmanager.com/ns.html?id=GTM-MBQ25R', $output);
    }
}
