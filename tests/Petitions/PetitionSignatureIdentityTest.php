<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

final class PetitionSignatureRedirect extends RuntimeException
{
}

/**
 * Signing a petition must give the same outcome whether or not the address
 * is already a signer: the answer may not tell who has signed before. An
 * existing signer's identity is never rewritten from the form.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PetitionSignatureIdentityTest extends TestCase
{
    private const CLH_PETITION_ID = 101;
    private const PETITION_ID = 102;
    private const KNOWN = 'known@example.test';
    private const UNKNOWN = 'unknown@example.test';
    private const TUNNEL_URL = 'https://wordpress.example.test/campagne/tunnel/';
    private const PETITION_URL = 'https://wordpress.example.test/petitions/une-petition/';

    protected function setUp(): void
    {
        if (!defined('DAY_IN_SECONDS')) {
            define('DAY_IN_SECONDS', 86400);
        }

        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('wp_redirect')) {
            function wp_redirect(string $location, int $status = 302): never
            {
                throw new PetitionSignatureRedirect($location);
            }
        }

        if (!function_exists('wp_get_referer')) {
            function wp_get_referer(): string|false
            {
                return false;
            }
        }

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return false;
            }
        }

        if (!function_exists('get_query_var')) {
            function get_query_var(string $query_var): mixed
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

        if (!function_exists('is_ssl')) {
            function is_ssl(): bool
            {
                return true;
            }
        }

        if (!function_exists('wp_json_encode')) {
            function wp_json_encode(mixed $data): string|false
            {
                return json_encode($data);
            }
        }

        if (!function_exists('trailingslashit')) {
            function trailingslashit(string $value): string
            {
                return rtrim($value, '/') . '/';
            }
        }

        if (!function_exists('get_the_title')) {
            function get_the_title(int $post_id = 0): string
            {
                return 'Une pétition';
            }
        }

        if (!function_exists('get_post_meta')) {
            function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
            {
                return 'type' === $key ? 'petition' : 0;
            }
        }

        if (!function_exists('update_post_meta')) {
            function update_post_meta(int $post_id, string $key, mixed $value): bool
            {
                return true;
            }
        }

        // Local signers: $GLOBALS['__phpunit_signers'][email] = id.
        if (!function_exists('get_local_user')) {
            function get_local_user(string $email): object|false
            {
                $id = $GLOBALS['__phpunit_signers'][$email] ?? null;

                return null === $id ? false : (object) ['id' => $id, 'email' => $email];
            }
        }

        if (!function_exists('have_signed')) {
            function have_signed(int $petition_id, int $user_id): bool
            {
                return in_array([$petition_id, $user_id], $GLOBALS['__phpunit_signatures'], true);
            }
        }

        if (!function_exists('insert_user')) {
            function insert_user(mixed ...$fields): int
            {
                $GLOBALS['__phpunit_inserted_signers'][] = $fields;

                return 99;
            }
        }

        if (!function_exists('insert_petition_signature')) {
            function insert_petition_signature(int $petition_id, int|string $user_id, string $date, mixed $code_origine, string $message): bool
            {
                $GLOBALS['__phpunit_signatures'][] = [$petition_id, (int) $user_id];

                return true;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $clh_petition = new WP_Post();
        $clh_petition->ID = self::CLH_PETITION_ID;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$clh_petition];
        $tunnel_page = new WP_Post();
        $tunnel_page->ID = 20;
        $GLOBALS['__phpunit_clh_tunnel_page'] = $tunnel_page;
        $GLOBALS['__phpunit_post_permalinks'][$tunnel_page->ID] = self::TUNNEL_URL;
        $GLOBALS['__phpunit_post_permalinks'][self::PETITION_ID] = self::PETITION_URL;
        $GLOBALS['__phpunit_post_permalinks'][self::CLH_PETITION_ID] = self::PETITION_URL;
        $GLOBALS['post'] = null;

        $GLOBALS['__phpunit_signers'] = [self::KNOWN => 7];
        $GLOBALS['__phpunit_signatures'] = [];
        $GLOBALS['__phpunit_inserted_signers'] = [];

        // An active session makes amnesty_start_secure_session() a no-op.
        ini_set('session.use_cookies', '0');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION = [];
        $_COOKIE = [];
    }

    private function sign(array $post): string
    {
        $_POST = $post + ['sign_petition' => '1'];

        try {
            amnesty_handle_petition_signature();
        } catch (PetitionSignatureRedirect $redirect) {
            return $redirect->getMessage();
        }

        self::fail('The signature handler did not redirect.');
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function petitions(): iterable
    {
        yield 'petition page' => [self::PETITION_ID, self::PETITION_URL];
        yield 'CLH petition page' => [self::CLH_PETITION_ID, self::PETITION_URL];
    }

    #[DataProvider('petitions')]
    public function testSignatureWithoutNamesIsRefusedForAKnownAndAnUnknownAddress(int $petition_id, string $form_url): void
    {
        $known = $this->sign(['petition_id' => (string) $petition_id, 'user_email' => self::KNOWN]);
        $unknown = $this->sign(['petition_id' => (string) $petition_id, 'user_email' => self::UNKNOWN]);

        self::assertSame($form_url . '?signature_status=missing_fields', $known);
        self::assertSame($known, $unknown);
        self::assertSame([], $GLOBALS['__phpunit_signatures']);
        self::assertSame([], $GLOBALS['__phpunit_inserted_signers']);
    }

    #[DataProvider('petitions')]
    public function testSignatureWithNamesGivesTheSameRedirectForAKnownAndAnUnknownAddress(int $petition_id, string $form_url): void
    {
        $identity = ['user_firstname' => 'Ada', 'user_lastname' => 'Lovelace'];

        $known = $this->sign(['petition_id' => (string) $petition_id, 'user_email' => self::KNOWN] + $identity);
        $unknown = $this->sign(['petition_id' => (string) $petition_id, 'user_email' => self::UNKNOWN] + $identity);
        // Signing again gives the same answer as a first signature.
        $again = $this->sign(['petition_id' => (string) $petition_id, 'user_email' => self::KNOWN] + $identity);

        self::assertSame($known, $unknown);
        self::assertSame($known, $again);
        self::assertStringNotContainsString('alreadysigned', $again);
        self::assertSame([[$petition_id, 7], [$petition_id, 99]], $GLOBALS['__phpunit_signatures']);
    }

    public function testFormFieldsDoNotRewriteAKnownSignersIdentity(): void
    {
        $this->sign([
            'petition_id' => (string) self::PETITION_ID,
            'user_email' => self::KNOWN,
            'user_firstname' => 'Someone',
            'user_lastname' => 'Else',
        ]);

        self::assertSame([[self::PETITION_ID, 7]], $GLOBALS['__phpunit_signatures']);
        self::assertSame([], $GLOBALS['__phpunit_inserted_signers']);
    }

    public function testTunnelOneClickSignatureStillWorksForThisBrowsersSigner(): void
    {
        $_SESSION['clh_last_signer_email'] = self::KNOWN;

        $result = $this->sign(['petition_id' => (string) self::CLH_PETITION_ID, 'from_tunnel' => '1', 'user_email' => self::KNOWN]);

        self::assertStringStartsWith(self::TUNNEL_URL, $result);
        self::assertStringNotContainsString('signature_status', $result);
        self::assertSame([[self::CLH_PETITION_ID, 7]], $GLOBALS['__phpunit_signatures']);
    }

    public function testSkippingWithAnAddressDoesNotEnableOneClickSignatureForIt(): void
    {
        $GLOBALS['__phpunit_valid_nonces'] = ['skip-nonce'];
        $outcomes = [];

        foreach ([self::KNOWN, self::UNKNOWN] as $email) {
            $_SESSION = [];
            $_POST = [
                'skip_petition' => '1',
                'petition_id' => (string) self::CLH_PETITION_ID,
                'clh_skip_nonce' => 'skip-nonce',
                'user_email' => $email,
            ];
            try {
                amnesty_handle_petition_skip();
            } catch (PetitionSignatureRedirect) {
            }

            $outcomes[$email] = $this->sign(['petition_id' => (string) self::CLH_PETITION_ID, 'from_tunnel' => '1', 'user_email' => $email]);
        }

        self::assertSame(self::TUNNEL_URL . '?signature_status=missing_fields', $outcomes[self::KNOWN]);
        self::assertSame($outcomes[self::KNOWN], $outcomes[self::UNKNOWN]);
        self::assertSame([], $GLOBALS['__phpunit_signatures']);
    }

    public function testTunnelSignatureWithoutNamesForAnotherAddressIsRefusedLikeAnUnknownOne(): void
    {
        $_SESSION['clh_last_signer_email'] = 'visitor@example.test';
        $GLOBALS['__phpunit_signers']['visitor@example.test'] = 8;

        $known = $this->sign(['petition_id' => (string) self::CLH_PETITION_ID, 'from_tunnel' => '1', 'user_email' => self::KNOWN]);
        $unknown = $this->sign(['petition_id' => (string) self::CLH_PETITION_ID, 'from_tunnel' => '1', 'user_email' => self::UNKNOWN]);

        self::assertSame(self::TUNNEL_URL . '?signature_status=missing_fields', $known);
        self::assertSame($known, $unknown);
        self::assertSame([], $GLOBALS['__phpunit_signatures']);
    }
}
