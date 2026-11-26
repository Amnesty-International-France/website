<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The tunnel's « Passer la pétition » buttons, desktop and mobile, carry the slug
 * of the petition they skip, so Amnesty reads it directly in its reports.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TunnelClhSkipButtonPatternTest extends TestCase
{
    private const THEME = '/wp-content/themes/humanity-theme';

    protected function setUp(): void
    {
        require_once __DIR__ . '/stubs.php';

        $GLOBALS['__phpunit_partials'] = [];
        $GLOBALS['post'] = (object) ['ID' => 20, 'post_parent' => 10];
        $GLOBALS['__phpunit_clh_context'] = [
            'active_campaign' => (object) ['ID' => 10],
            'last_signer_email' => 'someone@example.test',
            'list_petitions_clh' => [(object) ['ID' => 101]],
            'selected_posts' => [],
            'signed_count' => 0,
            'next_petition' => [
                'id' => 101,
                'slug' => 'liberez-ahmed',
                'title' => 'Libérez Ahmed',
                'description' => '',
                'image_id' => 0,
                'letter' => '',
                'goal' => 200000,
                'current' => 10,
                'percentage' => 0,
            ],
        ];

        if (!function_exists('amnesty_get_requested_signature_status_message')) {
            function amnesty_get_requested_signature_status_message(): string
            {
                return '';
            }
        }

        if (!function_exists('amnesty_get_clh_tunnel_context')) {
            function amnesty_get_clh_tunnel_context(object $post): array
            {
                return $GLOBALS['__phpunit_clh_context'];
            }
        }

        if (!function_exists('amnesty_is_clh_tunnel_turnstile_verified')) {
            function amnesty_is_clh_tunnel_turnstile_verified(): bool
            {
                return true;
            }
        }

        if (!function_exists('get_template_part')) {
            function get_template_part(string $slug, ?string $name = null, array $args = []): void
            {
            }
        }

        if (!function_exists('amnesty_clh_skip_nonce_field')) {
            function amnesty_clh_skip_nonce_field(): void
            {
            }
        }

        if (!function_exists('number_format_i18n')) {
            function number_format_i18n(float|int $number): string
            {
                return number_format($number);
            }
        }

        if (!function_exists('wp_strip_all_tags')) {
            function wp_strip_all_tags(string $text): string
            {
                return strip_tags($text);
            }
        }

        if (!function_exists('wp_kses_post')) {
            function wp_kses_post(string $text): string
            {
                return $text;
            }
        }
    }

    private function render(): DOMXPath
    {
        ob_start();
        require dirname(__DIR__, 2) . self::THEME . '/patterns/page-tunnel-clh-content.php';
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . ob_get_clean(), LIBXML_NOERROR);

        return new DOMXPath($document);
    }

    public function testSkipButtonsCarryThePetitionSlug(): void
    {
        $buttons = $this->render()->query('//button[@name="skip_petition"]');

        self::assertSame(2, $buttons->length);

        foreach ($buttons as $button) {
            self::assertSame('liberez-ahmed', $button->getAttribute('data-petition-name'));
            // The existing tracking attributes stay as they were.
            self::assertSame('clic_cta', $button->getAttribute('data-gtm'));
            self::assertSame('passer la petition', $button->getAttribute('data-name'));
            self::assertSame('101', $button->getAttribute('data-value'));
        }
    }

    public function testMobileSkipButtonCarriesThePetitionSlug(): void
    {
        $buttons = $this->render()->query('//button[@name="skip_petition"][contains(@class, "mobile")]');

        self::assertSame(1, $buttons->length);
        self::assertSame('liberez-ahmed', $buttons->item(0)->getAttribute('data-petition-name'));
    }
}
