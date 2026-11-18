<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Turnstile is on unless TURNSTILE_ENABLED=0. When off, forms are accepted without
 * a token and no Turnstile script is loaded.
 *
 * Other suites stub verify_turnstile() and aif_turnstile_site_key(), so each test
 * runs in its own process to load the real functions.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TurnstileSwitchTest extends TestCase
{
    private const SITE_KEY = 'site-key';
    private const CLOUDFLARE_ORIGIN = 'https://challenges.cloudflare.com';

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_turnstile_requests'] = [];
        $GLOBALS['__phpunit_turnstile_answer'] = ['success' => true];
        $GLOBALS['__phpunit_enqueued_scripts'] = [];

        function wp_remote_post(string $url, array $args): array
        {
            $GLOBALS['__phpunit_turnstile_requests'][] = $args['body'];

            return ['body' => json_encode($GLOBALS['__phpunit_turnstile_answer'])];
        }

        function wp_get_theme(): object
        {
            return new class () {
                public function get(string $header): string
                {
                    return '1.0.0';
                }
            };
        }

        function get_template_directory_uri(): string
        {
            return 'https://wordpress.example.test/wp-content/themes/humanity-theme';
        }

        function wp_enqueue_script(string $handle): void
        {
            $GLOBALS['__phpunit_enqueued_scripts'][] = $handle;
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/features/turnstile.php';

        putenv('TURNSTILE_SITE_KEY=' . self::SITE_KEY);
        putenv('TURNSTILE_SECRET_KEY=secret-key');
        $_POST = [];
    }

    public function testDisabledTurnstileAcceptsSubmissionWithoutTokenOrHttpCall(): void
    {
        putenv('TURNSTILE_ENABLED=0');

        self::assertNull(verify_turnstile());
        self::assertSame([], $GLOBALS['__phpunit_turnstile_requests']);
    }

    public function testDisabledTurnstileDoesNotSendAPostedTokenToCloudflare(): void
    {
        putenv('TURNSTILE_ENABLED=0');
        // A page cached before the switch still posts a token.
        $_POST['cf-turnstile-response'] = 'token-from-cached-page';

        self::assertNull(verify_turnstile());
        self::assertSame([], $GLOBALS['__phpunit_turnstile_requests']);
    }

    public function testDisabledTurnstileHasNoSiteKey(): void
    {
        putenv('TURNSTILE_ENABLED=0');

        self::assertFalse(aif_turnstile_enabled());
        self::assertSame('', aif_turnstile_site_key());
    }

    public function testDisabledTurnstileLoadsNoScriptAndNoPreconnect(): void
    {
        putenv('TURNSTILE_ENABLED=0');

        self::enqueueScripts();

        self::assertSame([], $GLOBALS['__phpunit_enqueued_scripts']);
        self::assertSame([], self::preconnectUrls());
    }

    #[DataProvider('enabledCases')]
    public function testEnabledTurnstileKeepsProtection(?string $enabled): void
    {
        putenv(null === $enabled ? 'TURNSTILE_ENABLED' : 'TURNSTILE_ENABLED=' . $enabled);

        self::enqueueScripts();

        self::assertTrue(aif_turnstile_enabled());
        self::assertSame(self::SITE_KEY, aif_turnstile_site_key());
        self::assertSame(['aif-turnstile', 'cf-turnstile'], $GLOBALS['__phpunit_enqueued_scripts']);
        self::assertSame([self::CLOUDFLARE_ORIGIN], self::preconnectUrls());
        self::assertSame('missing-input-response', verify_turnstile());
        self::assertSame([], $GLOBALS['__phpunit_turnstile_requests']);
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function enabledCases(): iterable
    {
        yield 'unset' => [null];
        yield 'empty' => [''];
        yield 'one' => ['1'];
        yield 'false' => ['false'];
        yield 'zero twice' => ['00'];
    }

    /**
     * @param array<string, mixed> $answer
     */
    #[DataProvider('cloudflareAnswerCases')]
    public function testEnabledTurnstileSendsTheTokenToCloudflare(array $answer, ?string $expected): void
    {
        putenv('TURNSTILE_ENABLED');
        $GLOBALS['__phpunit_turnstile_answer'] = $answer;
        $_POST['cf-turnstile-response'] = 'token-from-widget';

        self::assertSame($expected, verify_turnstile());
        self::assertCount(1, $GLOBALS['__phpunit_turnstile_requests']);
        self::assertSame('token-from-widget', $GLOBALS['__phpunit_turnstile_requests'][0]['response']);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, ?string}>
     */
    public static function cloudflareAnswerCases(): iterable
    {
        yield 'valid token' => [['success' => true], null];
        yield 'rejected token' => [['success' => false, 'error-codes' => ['invalid-input-response']], 'invalid-input-response'];
    }

    private static function enqueueScripts(): void
    {
        self::assertArrayHasKey('wp_enqueue_scripts', $GLOBALS['__phpunit_registered_actions']);

        foreach ($GLOBALS['__phpunit_registered_actions']['wp_enqueue_scripts'] as $callback) {
            $callback();
        }
    }

    /**
     * @return list<string>
     */
    private static function preconnectUrls(): array
    {
        self::assertArrayHasKey('wp_resource_hints', $GLOBALS['__phpunit_registered_filters']);

        $urls = [];
        foreach ($GLOBALS['__phpunit_registered_filters']['wp_resource_hints'] as $callback) {
            $urls = $callback($urls, 'preconnect');
        }

        return $urls;
    }
}
