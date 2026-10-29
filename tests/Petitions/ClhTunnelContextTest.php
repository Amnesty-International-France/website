<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The CLH tunnel shows which campaign petitions were already signed. That state
 * must only come from this browser's own signatures, never from an email
 * address read from a cookie or set by the visitor.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelContextTest extends TestCase
{
    private const VICTIM = 'someone@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('get_post_meta')) {
            function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
            {
                return 0;
            }
        }

        if (!function_exists('get_the_excerpt')) {
            function get_the_excerpt(int $post_id = 0): string
            {
                return '';
            }
        }

        if (!function_exists('get_post_thumbnail_id')) {
            function get_post_thumbnail_id(int $post_id = 0): int
            {
                return 0;
            }
        }

        if (!function_exists('get_local_user')) {
            function get_local_user(string $email): object|false
            {
                $GLOBALS['__phpunit_local_user_lookups'][] = $email;

                return (object) ['id' => 7];
            }
        }

        if (!function_exists('have_signed')) {
            function have_signed(int $petition_id, int $user_id): bool
            {
                return true;
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_url')) {
            function amnesty_get_clh_petition_tunnel_url(): string
            {
                return 'https://example.test/campagne/';
            }
        }

        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(): ?string
            {
                return null;
            }
        }

        if (!function_exists('wp_json_encode')) {
            function wp_json_encode(mixed $data): string|false
            {
                return json_encode($data);
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$this->petition(101), $this->petition(102), $this->petition(103)];
        $GLOBALS['__phpunit_local_user_lookups'] = [];
        // An active session makes amnesty_start_secure_session() a no-op.
        ini_set('session.use_cookies', '0');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION = [];
        $_COOKIE = [];
    }

    private function petition(int $id): WP_Post
    {
        $petition = new WP_Post();
        $petition->ID = $id;
        $petition->post_title = 'Pétition ' . $id;

        return $petition;
    }

    private function tunnelPage(): WP_Post
    {
        $page = new WP_Post();
        $page->ID = 20;
        $page->post_parent = 10;

        return $page;
    }

    /** @return array<int,bool> */
    private function signedStates(array $context): array
    {
        return array_column($context['selected_posts'], 'already_signed', 'id');
    }

    public function testEmailCookieDoesNotRevealSignatures(): void
    {
        $_COOKIE['clh_user_email'] = self::VICTIM;

        $context = amnesty_get_clh_tunnel_context($this->tunnelPage());

        self::assertSame([101 => false, 102 => false, 103 => false], $this->signedStates($context));
        self::assertSame(0, $context['signed_count']);
        self::assertNull($context['last_signer_email']);
        self::assertSame([], $GLOBALS['__phpunit_local_user_lookups']);
    }

    public function testLegacyEmailCookieIsClearedWithoutSigningAgain(): void
    {
        $_COOKIE['clh_user_email'] = self::VICTIM;

        amnesty_expire_clh_legacy_email_cookie();

        self::assertArrayNotHasKey('clh_user_email', $_COOKIE);
    }

    public function testSkippingDoesNotSetTheSignerEmail(): void
    {
        $GLOBALS['__phpunit_valid_nonces'] = ['nonce'];
        $_POST = [
            'skip_petition'  => '',
            'petition_id'    => '101',
            'clh_skip_nonce' => 'nonce',
            'user_email'     => self::VICTIM,
        ];

        try {
            amnesty_handle_petition_skip();
            self::fail('The skip handler should redirect.');
        } catch (ClhRedirect $redirect) {
            self::assertSame('https://example.test/campagne/', $redirect->getMessage());
        }

        self::assertSame([101], amnesty_get_clh_skipped_petitions());
        self::assertArrayNotHasKey('clh_last_signer_email', $_SESSION);
    }

    public function testSessionEmailDoesNotRevealSignatures(): void
    {
        // Set after signing with an address the visitor typed.
        $_SESSION['clh_last_signer_email'] = self::VICTIM;

        $context = amnesty_get_clh_tunnel_context($this->tunnelPage());

        self::assertSame([101 => false, 102 => false, 103 => false], $this->signedStates($context));
        self::assertSame(self::VICTIM, $context['last_signer_email']);
        self::assertSame([], $GLOBALS['__phpunit_local_user_lookups']);
    }

    public function testPetitionsSignedFromThisBrowserAreShownAsSigned(): void
    {
        $_SESSION['clh_last_signer_email'] = self::VICTIM;
        $_COOKIE['clh_signed_petitions'] = '[102]';

        $context = amnesty_get_clh_tunnel_context($this->tunnelPage());

        self::assertSame([101 => false, 102 => true, 103 => false], $this->signedStates($context));
        self::assertSame(1, $context['signed_count']);
        self::assertNotSame(102, $context['next_petition']['id']);
    }

    public function testConcurrentSignatureResponsesKeepBothPetitions(): void
    {
        $initial_cookies = ['clh_signed_petitions' => '[101]'];

        $_COOKIE = $initial_cookies;
        amnesty_set_clh_signed_petition(102);
        $first_response_cookies = array_diff_key($_COOKIE, $initial_cookies);

        $_COOKIE = $initial_cookies;
        amnesty_set_clh_signed_petition(103);
        $second_response_cookies = array_diff_key($_COOKIE, $initial_cookies);

        $_COOKIE = array_merge($initial_cookies, $first_response_cookies, $second_response_cookies);

        $context = amnesty_get_clh_tunnel_context($this->tunnelPage());

        self::assertSame([101 => true, 102 => true, 103 => true], $this->signedStates($context));
        self::assertSame(3, $context['signed_count']);
        self::assertNull($context['next_petition']);
    }

    public function testBrowserProgressIgnoresSignaturesFromOtherDevices(): void
    {
        self::assertSame(0, amnesty_get_clh_signed_petition_count_for_browser());

        $_COOKIE['clh_signed_petition_102'] = '1';

        self::assertSame(1, amnesty_get_clh_signed_petition_count_for_browser());
    }
}
