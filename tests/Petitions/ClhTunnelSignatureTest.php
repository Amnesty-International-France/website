<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * A signature posted to the CLH tunnel must neither sign with an address other than
 * the one this browser signed with, nor tell whether an address already signed.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelSignatureTest extends TestCase
{
    private const TUNNEL_URL = 'https://wordpress.example.test/campagne/tunnel/';
    private const SIGNER = 'signer@example.test';
    private const VICTIM = 'victim@example.test';
    private const NEWCOMER = 'newcomer@example.test';
    private const USER_IDS = [self::SIGNER => 7, self::VICTIM => 8, self::NEWCOMER => 9];

    protected function setUp(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return false;
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_url')) {
            function amnesty_get_clh_petition_tunnel_url(): string
            {
                return 'https://wordpress.example.test/campagne/tunnel/';
            }
        }

        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(): ?string
            {
                return null;
            }
        }

        if (!function_exists('get_post_meta')) {
            function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
            {
                return 0;
            }
        }

        if (!function_exists('update_post_meta')) {
            function update_post_meta(int $post_id, string $key, mixed $value): bool
            {
                return true;
            }
        }

        if (!function_exists('get_the_title')) {
            function get_the_title(int $post_id = 0): string
            {
                return 'Pétition ' . $post_id;
            }
        }

        if (!function_exists('get_local_user')) {
            function get_local_user(string $email): object|false
            {
                $GLOBALS['__phpunit_local_user_lookups'][] = $email;
                $user_id = $GLOBALS['__phpunit_local_user_ids'][$email] ?? null;

                return $user_id === null ? false : (object) ['id' => $user_id];
            }
        }

        if (!function_exists('have_signed')) {
            function have_signed(int $petition_id, int $user_id): bool
            {
                return in_array([$petition_id, $user_id], $GLOBALS['__phpunit_signatures'], true);
            }
        }

        if (!function_exists('insert_petition_signature')) {
            function insert_petition_signature(int $petition_id, int $user_id, string $date_signature, string $code_origine, string $message): int|false
            {
                $GLOBALS['__phpunit_signatures'][] = [$petition_id, $user_id];

                return 1;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$this->petition(101), $this->petition(102)];
        $GLOBALS['__phpunit_local_user_ids'] = self::USER_IDS;
        $GLOBALS['__phpunit_local_user_lookups'] = [];
        $GLOBALS['__phpunit_signatures'] = [[102, self::USER_IDS[self::VICTIM]]];
        $GLOBALS['post'] = null;
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

        return $petition;
    }

    /**
     * Posts a one-click tunnel signature and returns what the browser gets back.
     *
     * @return array{location: string, cookies: array<string,string>, session: array<string,mixed>}
     */
    private function signFromTunnel(int $petition_id, string $email): array
    {
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) $petition_id,
            'from_tunnel' => '1',
            'user_email' => $email,
        ];

        try {
            amnesty_handle_petition_signature();
            self::fail('The signature handler did not redirect.');
        } catch (ClhRedirect $redirect) {
            return ['location' => $redirect->getMessage(), 'cookies' => $_COOKIE, 'session' => $_SESSION];
        }
    }

    public function testTunnelSignsWithTheSessionEmailOnly(): void
    {
        $_SESSION['clh_last_signer_email'] = self::SIGNER;

        $this->signFromTunnel(101, self::VICTIM);

        self::assertSame([self::SIGNER], $GLOBALS['__phpunit_local_user_lookups']);
        self::assertContains([101, self::USER_IDS[self::SIGNER]], $GLOBALS['__phpunit_signatures']);
        self::assertNotContains([101, self::USER_IDS[self::VICTIM]], $GLOBALS['__phpunit_signatures']);
        self::assertSame(self::SIGNER, $_SESSION['clh_last_signer_email']);
    }

    public function testAddressThatAlreadySignedGetsTheResponseOfANewSignature(): void
    {
        $already_signed = $this->signFromTunnel(102, self::VICTIM);

        self::assertSame([[102, self::USER_IDS[self::VICTIM]]], $GLOBALS['__phpunit_signatures']);

        $_SESSION = [];
        $_COOKIE = [];
        $new_signature = $this->signFromTunnel(102, self::NEWCOMER);

        self::assertContains([102, self::USER_IDS[self::NEWCOMER]], $GLOBALS['__phpunit_signatures']);
        self::assertStringNotContainsString('alreadysigned', $already_signed['location']);
        self::assertSame($new_signature['location'], $already_signed['location']);
        self::assertSame(array_keys($new_signature['cookies']), array_keys($already_signed['cookies']));
        self::assertSame(array_keys($new_signature['session']), array_keys($already_signed['session']));
    }
}
