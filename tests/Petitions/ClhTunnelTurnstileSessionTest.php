<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

final class ClhTurnstileRedirect extends RuntimeException
{
}

/**
 * A Turnstile token is single-use: once a visitor has passed the check in the CLH
 * tunnel, the following tunnel submissions of the session skip it for 30 minutes.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelTurnstileSessionTest extends TestCase
{
    private const PETITION_ID = 101;
    private const TUNNEL_URL = 'https://wordpress.example.test/campagne/tunnel/';

    protected function setUp(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        function wp_redirect(string $location, int $status = 302): never
        {
            throw new ClhTurnstileRedirect($location);
        }

        function is_page(): bool
        {
            return $GLOBALS['post'] instanceof WP_Post;
        }

        function get_query_var(string $query_var): mixed
        {
            return '';
        }

        function get_page_template_slug(int $post_id): string
        {
            return '';
        }

        function amnesty_get_clh_petition_tunnel_page(): ?WP_Post
        {
            return $GLOBALS['__phpunit_clh_tunnel_page'];
        }

        function verify_turnstile(): ?string
        {
            $GLOBALS['__phpunit_turnstile_calls']++;

            return $GLOBALS['__phpunit_turnstile_error'];
        }

        function is_ssl(): bool
        {
            return false;
        }

        function wp_json_encode(mixed $value): string|false
        {
            return json_encode($value);
        }

        function turnstile_friendly_error(string $code): string
        {
            return $code;
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $tunnel_page = new WP_Post();
        $tunnel_page->ID = 20;
        $GLOBALS['__phpunit_clh_tunnel_page'] = $tunnel_page;
        $GLOBALS['__phpunit_post_permalinks'][$tunnel_page->ID] = self::TUNNEL_URL;
        $GLOBALS['__phpunit_turnstile_calls'] = 0;
        $GLOBALS['__phpunit_turnstile_error'] = null;
        $GLOBALS['__phpunit_valid_nonces'] = ['skip-nonce'];
        // Current page of the request; null outside any page.
        $GLOBALS['post'] = null;

        // An active session makes amnesty_start_secure_session() a no-op.
        ini_set('session.use_cookies', '0');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION = [];
    }

    public function testFirstSuccessfulCheckIsRememberedForTheSession(): void
    {
        self::assertNull(amnesty_verify_clh_tunnel_turnstile());
        self::assertNull(amnesty_verify_clh_tunnel_turnstile());

        self::assertSame(1, $GLOBALS['__phpunit_turnstile_calls']);
        self::assertTrue(amnesty_is_clh_tunnel_turnstile_verified());
    }

    public function testFailedCheckIsNotRemembered(): void
    {
        $GLOBALS['__phpunit_turnstile_error'] = 'invalid-input-response';

        self::assertSame('invalid-input-response', amnesty_verify_clh_tunnel_turnstile());
        self::assertFalse(amnesty_is_clh_tunnel_turnstile_verified());
    }

    public function testCheckIsRequiredAgainAfterThirtyMinutes(): void
    {
        $_SESSION['clh_turnstile_verified_at'] = time() - 30 * MINUTE_IN_SECONDS;

        self::assertFalse(amnesty_is_clh_tunnel_turnstile_verified());
        amnesty_verify_clh_tunnel_turnstile();
        self::assertSame(1, $GLOBALS['__phpunit_turnstile_calls']);
    }

    public function testCheckIsSkippedWithinThirtyMinutes(): void
    {
        $_SESSION['clh_turnstile_verified_at'] = time() - 29 * MINUTE_IN_SECONDS;

        self::assertTrue(amnesty_is_clh_tunnel_turnstile_verified());
        self::assertNull(amnesty_verify_clh_tunnel_turnstile());
        self::assertSame(0, $GLOBALS['__phpunit_turnstile_calls']);
    }

    public function testSkipInVerifiedSessionNeedsNoToken(): void
    {
        $_SESSION['clh_turnstile_verified_at'] = time();
        $GLOBALS['__phpunit_turnstile_error'] = 'missing-input-response';
        $_POST = ['skip_petition' => '1', 'petition_id' => (string) self::PETITION_ID, 'clh_skip_nonce' => 'skip-nonce'];

        self::assertSame(self::TUNNEL_URL, $this->redirectLocation('amnesty_handle_petition_skip'));
    }

    public function testSkipWithoutVerifiedSessionNeedsAToken(): void
    {
        $GLOBALS['__phpunit_turnstile_error'] = 'missing-input-response';
        $_POST = ['skip_petition' => '1', 'petition_id' => (string) self::PETITION_ID, 'clh_skip_nonce' => 'skip-nonce'];

        self::assertSame(self::TUNNEL_URL . '?signature_status=turnstile', $this->redirectLocation('amnesty_handle_petition_skip'));
    }

    public function testTunnelSignatureInVerifiedSessionNeedsNoToken(): void
    {
        $this->seedActiveCampaign();
        $_SESSION['clh_turnstile_verified_at'] = time();
        $GLOBALS['__phpunit_turnstile_error'] = 'missing-input-response';
        // No email anywhere: past the Turnstile check, the handler sends the visitor back as invalid.
        $_POST = ['sign_petition' => '1', 'petition_id' => (string) self::PETITION_ID, 'from_tunnel' => '1'];

        self::assertSame(self::TUNNEL_URL . '?signature_status=invalid', $this->redirectLocation('amnesty_handle_petition_signature'));
    }

    public function testSignatureOutsideTunnelIgnoresTheVerifiedSession(): void
    {
        $this->seedActiveCampaign();
        $_SESSION['clh_turnstile_verified_at'] = time();
        $GLOBALS['__phpunit_turnstile_error'] = 'missing-input-response';
        $_POST = ['sign_petition' => '1', 'petition_id' => (string) self::PETITION_ID, 'user_email' => 'jane@example.org'];

        amnesty_handle_petition_signature();

        self::assertSame(1, $GLOBALS['__phpunit_turnstile_calls']);
        self::assertArrayHasKey('petition_turnstile_error_message', $GLOBALS);
    }

    private function seedActiveCampaign(): void
    {
        $campaign = new WP_Post();
        $campaign->ID = 10;
        $petition = new WP_Post();
        $petition->ID = self::PETITION_ID;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$petition];
    }

    private function redirectLocation(callable $handler): string
    {
        try {
            $handler();
        } catch (ClhTurnstileRedirect $redirect) {
            return $redirect->getMessage();
        }

        self::fail('The handler did not redirect.');
    }
}
