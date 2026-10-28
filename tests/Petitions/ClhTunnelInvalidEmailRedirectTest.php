<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

final class ClhSignatureRedirect extends RuntimeException
{
}

/**
 * Only the CLH tunnel page reads `signature_status`: an invalid email typed in the
 * tunnel must bring the visitor back there, where the error message is shown.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelInvalidEmailRedirectTest extends TestCase
{
    private const PETITION_ID = 101;
    private const TUNNEL_URL = 'https://wordpress.example.test/campagne/tunnel/';
    private const PETITION_URL = 'https://wordpress.example.test/petitions/une-petition/';

    protected function setUp(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('wp_redirect')) {
            function wp_redirect(string $location, int $status = 302): never
            {
                throw new ClhSignatureRedirect($location);
            }
        }

        if (!function_exists('wp_get_referer')) {
            // The tunnel form posts back to its own URL, so WordPress finds no usable referer.
            function wp_get_referer(): string|false
            {
                return false;
            }
        }

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return $GLOBALS['post'] instanceof WP_Post;
            }
        }

        if (!function_exists('get_query_var')) {
            function get_query_var(string $query_var): mixed
            {
                return '';
            }
        }

        if (!function_exists('get_page_template_slug')) {
            function get_page_template_slug(int $post_id): string
            {
                return '';
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_page')) {
            function amnesty_get_clh_petition_tunnel_page(): ?WP_Post
            {
                return $GLOBALS['__phpunit_clh_tunnel_page'];
            }
        }

        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(): ?string
            {
                return null;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $petition = new WP_Post();
        $petition->ID = self::PETITION_ID;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$petition];
        $GLOBALS['__phpunit_post_permalinks'][self::PETITION_ID] = self::PETITION_URL;
        $tunnel_page = new WP_Post();
        $tunnel_page->ID = 20;
        $GLOBALS['__phpunit_clh_tunnel_page'] = $tunnel_page;
        $GLOBALS['__phpunit_post_permalinks'][$tunnel_page->ID] = self::TUNNEL_URL;
        // Current page of the request; null outside any page.
        $GLOBALS['post'] = null;
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) self::PETITION_ID,
        ];

        // A petition outside the active campaign is also sent back to the tunnel with the same
        // status, before the email is checked: without this, the tunnel tests could pass on that branch.
        self::assertTrue(amnesty_is_petition_in_active_clh_campaign(self::PETITION_ID));
    }

    private function redirectLocation(): string
    {
        try {
            amnesty_handle_petition_signature();
        } catch (ClhSignatureRedirect $redirect) {
            return $redirect->getMessage();
        }

        self::fail('The signature handler did not redirect.');
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailInTunnelRedirectsBackToTunnel(string $email): void
    {
        $_POST['user_email'] = $email;
        $_POST['from_tunnel'] = '1';

        self::assertSame(self::TUNNEL_URL . '?signature_status=invalid', $this->redirectLocation());
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailPostedToTunnelPageRedirectsBackToTunnel(string $email): void
    {
        $_POST['user_email'] = $email;
        $GLOBALS['post'] = $GLOBALS['__phpunit_clh_tunnel_page'];

        self::assertSame(self::TUNNEL_URL . '?signature_status=invalid', $this->redirectLocation());
    }

    #[DataProvider('sessionEmails')]
    public function testTunnelSubmissionWithoutUsableSessionEmailRedirectsBackToTunnel(?string $session_email): void
    {
        $_POST['from_tunnel'] = '1';
        // An active session makes amnesty_start_secure_session() a no-op.
        ini_set('session.use_cookies', '0');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION = $session_email === null ? [] : ['clh_last_signer_email' => $session_email];

        self::assertSame(self::TUNNEL_URL . '?signature_status=invalid', $this->redirectLocation());
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailOutsideTunnelRedirectsToPetitionPage(string $email): void
    {
        $_POST['user_email'] = $email;

        self::assertSame(self::PETITION_URL . '?signature_status=invalid', $this->redirectLocation());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidEmails(): iterable
    {
        yield 'domain without dot' => ['abc@localhost'];
        yield 'shorter than 6 characters' => ['a@b.c'];
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function sessionEmails(): iterable
    {
        yield 'no email in session' => [null];
        yield 'invalid email in session' => ['a@b'];
    }
}
