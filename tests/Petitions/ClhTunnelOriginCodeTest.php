<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * AIF-450: a visitor coming from a link with an origin code signs the next petitions of the
 * CLH tunnel with that code, not with the default code of each petition.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelOriginCodeTest extends TestCase
{
    private const SIGNER = 'signer@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return $GLOBALS['__phpunit_is_page'] ?? false;
            }
        }

        if (!function_exists('get_post')) {
            function get_post(): ?WP_Post
            {
                return $GLOBALS['post'] ?? null;
            }
        }

        if (!function_exists('wp_json_encode')) {
            function wp_json_encode(mixed $data): string|false
            {
                return json_encode($data);
            }
        }

        if (!function_exists('get_query_var')) {
            function get_query_var(string $key, mixed $default = ''): mixed
            {
                return $default;
            }
        }

        if (!function_exists('get_page_template_slug')) {
            function get_page_template_slug(int $post_id = 0): string
            {
                return '';
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_page')) {
            function amnesty_get_clh_petition_tunnel_page(): ?WP_Post
            {
                return $GLOBALS['__phpunit_clh_tunnel_page'] ?? null;
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_url')) {
            function amnesty_get_clh_petition_tunnel_url(): string
            {
                return 'https://wordpress.example.test/campagne/tunnel/';
            }
        }

        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(bool $switchable = false): ?string
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
                return (object) ['id' => 7];
            }
        }

        if (!function_exists('have_signed')) {
            function have_signed(int $petition_id, int $user_id): bool
            {
                return false;
            }
        }

        if (!function_exists('insert_petition_signature')) {
            function insert_petition_signature(int $petition_id, int $user_id, string $date_signature, string $code_origine, string $message): int|false
            {
                $GLOBALS['__phpunit_signature_codes'][$petition_id] = $code_origine;

                return 1;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$this->petition(101), $this->petition(102), $this->petition(103)];
        $GLOBALS['__phpunit_acf_field_values'] = [
            101 => ['code_origine' => 'DEFAULT101'],
            102 => ['code_origine' => 'DEFAULT102'],
            103 => ['code_origine' => 'DEFAULT103'],
        ];
        $GLOBALS['__phpunit_signature_codes'] = [];
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

    /** @param array<string,string> $fields */
    private function sign(int $petition_id, array $fields = []): void
    {
        $_POST = $fields + [
            'sign_petition' => '1',
            'petition_id' => (string) $petition_id,
            'user_email' => self::SIGNER,
        ];

        try {
            amnesty_handle_petition_signature();
            self::fail('The signature handler did not redirect.');
        } catch (ClhRedirect) {
        }
    }

    /** CA-450-01 */
    public function testTunnelSignatureAfterAnEmailingSendsTheEmailingCode(): void
    {
        // The petition page adds the code of its URL to the form (submitCodeOrigine).
        $this->sign(101, ['code_origine' => '26BJPE1786']);
        $this->sign(102, ['from_tunnel' => '1']);
        $this->sign(103, ['from_tunnel' => '1']);

        self::assertSame(
            [101 => '26BJPE1786', 102 => '26BJPE1786', 103 => '26BJPE1786'],
            $GLOBALS['__phpunit_signature_codes'],
        );
    }

    public function testTunnelSignatureWithoutIncomingCodeSendsThePetitionDefault(): void
    {
        $this->sign(101);
        $this->sign(102, ['from_tunnel' => '1']);

        self::assertSame([101 => 'DEFAULT101', 102 => 'DEFAULT102'], $GLOBALS['__phpunit_signature_codes']);
    }

    public function testCodeInTheTunnelUrlIsKeptForTheTunnelSignatures(): void
    {
        $page = new WP_Post();
        $page->ID = 20;
        $page->post_parent = 10;
        $GLOBALS['post'] = $page;
        $GLOBALS['__phpunit_clh_tunnel_page'] = $page;
        $GLOBALS['__phpunit_is_page'] = true;
        $_GET = ['code_origine' => 'CODEA'];

        amnesty_keep_clh_origin_code_from_tunnel_url();

        // The signature is posted like the tunnel form does, recognised by `from_tunnel` only.
        $GLOBALS['__phpunit_is_page'] = false;
        $this->sign(102, ['from_tunnel' => '1']);

        self::assertSame([102 => 'CODEA'], $GLOBALS['__phpunit_signature_codes']);
    }

    public function testSkippingAPetitionKeepsTheCodeLikeTheRestOfTheTunnelProgress(): void
    {
        // Kept 14 minutes ago: it would be forgotten one minute later without any activity.
        $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY] = ['code' => 'CODEA', 'at' => time() - AMNESTY_CLH_TUNNEL_STATE_TTL + 60];
        $GLOBALS['__phpunit_valid_nonces'] = ['nonce'];
        $_POST = ['skip_petition' => '', 'petition_id' => '101', 'clh_skip_nonce' => 'nonce'];

        try {
            amnesty_handle_petition_skip();
            self::fail('The skip handler did not redirect.');
        } catch (ClhRedirect) {
        }

        self::assertGreaterThanOrEqual(time() - 1, $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY]['at']);
        $this->sign(102, ['from_tunnel' => '1']);
        self::assertSame([102 => 'CODEA'], $GLOBALS['__phpunit_signature_codes']);
    }
}
