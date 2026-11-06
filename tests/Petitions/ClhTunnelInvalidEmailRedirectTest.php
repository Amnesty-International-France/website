<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ClhTunnelRedirectSetup.php';

/**
 * Only the CLH tunnel page reads `signature_status`: an invalid email typed in the
 * tunnel must bring the visitor back there, where the error message is shown above
 * the same petition, with the fields already typed.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelInvalidEmailRedirectTest extends TestCase
{
    use ClhTunnelRedirectSetup;

    private const TUNNEL_RETRY_URL = self::TUNNEL_URL . '?signature_status=invalid&clh_petition=101';
    private const TYPED_FIELDS = [
        'civility' => 'Mme',
        'user_firstname' => 'Ada',
        'user_lastname' => 'Lovelace',
        'user_zipcode' => '75001',
        'user_country' => 'France',
        'user_phone' => '0600000000',
    ];

    protected function setUp(): void
    {
        $this->setUpTunnelRedirect();
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) self::PETITION_ID,
        ] + self::TYPED_FIELDS;

        // A petition outside the active campaign is also sent back to the tunnel with the same
        // status, before the email is checked: without this, the tunnel tests could pass on that branch.
        self::assertTrue(amnesty_is_petition_in_active_clh_campaign(self::PETITION_ID));
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailInTunnelRedirectsBackToTunnel(string $email): void
    {
        $_POST['user_email'] = $email;
        $_POST['from_tunnel'] = '1';

        self::assertSame(self::TUNNEL_RETRY_URL, $this->redirectLocation());
        self::assertSame(['user_email' => $email] + self::TYPED_FIELDS, $_SESSION['clh_signature_form'] ?? null);
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailPostedToTunnelPageRedirectsBackToTunnel(string $email): void
    {
        $_POST['user_email'] = $email;
        $GLOBALS['post'] = $GLOBALS['__phpunit_clh_tunnel_page'];

        self::assertSame(self::TUNNEL_RETRY_URL, $this->redirectLocation());
        self::assertSame(['user_email' => $email] + self::TYPED_FIELDS, $_SESSION['clh_signature_form'] ?? null);
    }

    #[DataProvider('sessionEmails')]
    public function testTunnelSubmissionWithoutUsableSessionEmailRedirectsBackToTunnel(?string $session_email): void
    {
        // The signed-in tunnel form posts hidden inputs only.
        $_POST = [
            'sign_petition' => '1',
            'petition_id' => (string) self::PETITION_ID,
            'from_tunnel' => '1',
        ];
        $_SESSION = $session_email === null ? [] : ['clh_last_signer_email' => $session_email];

        self::assertSame(self::TUNNEL_RETRY_URL, $this->redirectLocation());
        self::assertSame(
            array_fill_keys(['user_email', ...array_keys(self::TYPED_FIELDS)], ''),
            $_SESSION['clh_signature_form'] ?? null,
        );
    }

    public function testTunnelPetitionLeftOutOfTheCampaignKeepsTypedFields(): void
    {
        // The petition stopped being part of the campaign while the visitor was typing.
        $_POST['petition_id'] = '999';
        $_POST['user_email'] = 'ada@example.com';
        $_POST['from_tunnel'] = '1';

        self::assertSame(self::TUNNEL_URL . '?signature_status=invalid&clh_petition=999', $this->redirectLocation());
        self::assertSame(['user_email' => 'ada@example.com'] + self::TYPED_FIELDS, $_SESSION['clh_signature_form'] ?? null);
    }

    #[DataProvider('invalidEmails')]
    public function testInvalidEmailOutsideTunnelRedirectsToPetitionPage(string $email): void
    {
        $_POST['user_email'] = $email;

        self::assertSame(self::PETITION_URL . '?signature_status=invalid', $this->redirectLocation());
        self::assertArrayNotHasKey('clh_signature_form', $_SESSION);
    }

    public function testMissingEmailOutsideTunnelRedirectsToPetitionPage(): void
    {
        self::assertSame(self::PETITION_URL . '?signature_status=invalid', $this->redirectLocation());
        self::assertArrayNotHasKey('clh_signature_form', $_SESSION);
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
