<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/SignatureErrorRedirectSetup.php';

/**
 * A new signer who leaves their first or last name empty in the CLH tunnel must get
 * the error above the same petition, with the fields already typed.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelMissingFieldsRedirectTest extends TestCase
{
    use SignatureErrorRedirectSetup;

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
