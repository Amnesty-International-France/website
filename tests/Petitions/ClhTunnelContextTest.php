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
        if (!defined('DAY_IN_SECONDS')) {
            define('DAY_IN_SECONDS', 86400);
        }

        if (!class_exists('WP_Post')) {
            eval('class WP_Post { public int $ID = 0; public int $post_parent = 0; public string $post_title = ""; }');
        }

        if (!class_exists('WP_Query')) {
            eval('class WP_Query { public array $posts; public function __construct(array $args) { $this->posts = $GLOBALS["__phpunit_clh_petitions"]; } }');
        }

        if (!function_exists('is_ssl')) {
            function is_ssl(): bool
            {
                return true;
            }
        }

        if (!function_exists('is_admin')) {
            function is_admin(): bool
            {
                return false;
            }
        }

        if (!function_exists('amnesty_get_clh_petition_campaign_page')) {
            function amnesty_get_clh_petition_campaign_page(): ?WP_Post
            {
                return $GLOBALS['__phpunit_clh_campaign'] ?? null;
            }

            function amnesty_get_active_clh_campaign_for_page(int $page_id): ?WP_Post
            {
                return $GLOBALS['__phpunit_clh_campaign'];
            }
        }

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

        if (!function_exists('get_permalink')) {
            function get_permalink(int $post_id = 0): string
            {
                return 'https://wordpress.example.test/?p=' . $post_id;
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

    public function testSessionEmailDoesNotRevealSignatures(): void
    {
        // Set by "passer" from an address typed by the visitor.
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
