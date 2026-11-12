<?php

declare(strict_types=1);

require_once __DIR__ . '/ClhTunnelRedirectSetup.php';

/**
 * Setup shared by the tests of a new signer's signature rejected after the email check:
 * missing name, petition ended, signer or signature not stored. Tests pick the failure
 * with failAt(); without it, creating the signer is unexpected and fails the test.
 */
trait SignatureErrorRedirectSetup
{
    use ClhTunnelRedirectSetup;

    private const TYPED_FIELDS = [
        'user_email' => 'new.signer@example.test',
        'civility' => 'Autre',
        'user_firstname' => 'Ada',
        'user_lastname' => 'Lovelace',
        'user_zipcode' => '75001',
        'user_country' => 'France',
        'user_phone' => '0600000000',
    ];

    protected function setUp(): void
    {
        if (!function_exists('get_post_meta')) {
            function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
            {
                return '';
            }
        }

        if (!function_exists('get_local_user')) {
            // The address is unknown: the handler creates the signer first.
            function get_local_user(string $email): object|false
            {
                return false;
            }
        }

        if (!function_exists('insert_user')) {
            function insert_user(mixed ...$fields): int|false
            {
                return match ($GLOBALS['__phpunit_failing_step'] ?? null) {
                    null => throw new LogicException('The signer must not be created.'),
                    'user' => false,
                    default => 7,
                };
            }
        }

        if (!function_exists('insert_petition_signature')) {
            function insert_petition_signature(mixed ...$fields): int|false
            {
                if (($GLOBALS['__phpunit_failing_step'] ?? null) === 'signature') {
                    return false;
                }

                throw new LogicException('The signature must be rejected before it is stored.');
            }
        }

        $this->setUpTunnelRedirect();
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) self::PETITION_ID,
        ] + self::TYPED_FIELDS;
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rejectedSignatures(): iterable
    {
        yield 'petition ended' => ['expired', 'expired'];
        yield 'signer not stored' => ['user', 'error'];
        yield 'signature not stored' => ['signature', 'error'];
    }

    private function failAt(string $failing_step): void
    {
        $GLOBALS['__phpunit_failing_step'] = $failing_step;

        if ($failing_step === 'expired') {
            $GLOBALS['__phpunit_acf_field_values'][self::PETITION_ID]['date_de_fin'] = '2000-01-01';
        }
    }
}
