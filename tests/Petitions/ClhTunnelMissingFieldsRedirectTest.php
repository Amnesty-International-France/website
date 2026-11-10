<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ClhTunnelRedirectSetup.php';

/**
 * A new signer who leaves their first or last name empty in the CLH tunnel must get
 * the error above the same petition, with the fields already typed.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelMissingFieldsRedirectTest extends TestCase
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
            // The address is unknown: the handler asks for the new signer's identity.
            function get_local_user(string $email): object|false
            {
                return false;
            }
        }

        if (!function_exists('insert_user')) {
            function insert_user(mixed ...$fields): int|false
            {
                throw new LogicException('A signer without first or last name must not be created.');
            }
        }

        $this->setUpTunnelRedirect();
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) self::PETITION_ID,
        ] + self::TYPED_FIELDS;
    }

    #[DataProvider('missingNames')]
    public function testMissingNameInTunnelRedirectsBackToSamePetitionWithTypedFields(string $field): void
    {
        $_POST['from_tunnel'] = '1';
        $_POST[$field] = '';

        self::assertSame(
            self::TUNNEL_URL . '?signature_status=missing_fields&clh_petition=101',
            $this->redirectLocation(),
        );
        self::assertSame(array_replace(self::TYPED_FIELDS, [$field => '']), $_SESSION['clh_signature_form'] ?? null);
    }

    public function testMissingNameOutsideTunnelRedirectsToPetitionPageWithoutStoringFields(): void
    {
        $_POST['user_lastname'] = '';

        self::assertSame(self::PETITION_URL . '?signature_status=missing_fields', $this->redirectLocation());
        self::assertArrayNotHasKey('clh_signature_form', $_SESSION);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function missingNames(): iterable
    {
        yield 'first name' => ['user_firstname'];
        yield 'last name' => ['user_lastname'];
    }
}
