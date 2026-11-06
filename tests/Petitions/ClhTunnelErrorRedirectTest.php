<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/SignatureErrorRedirectSetup.php';

/**
 * A signature rejected after the email check (petition ended, signer or signature not
 * stored) must bring a tunnel visitor back to the same petition, with the fields already
 * typed, and a petition-page visitor back to the petition page.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTunnelErrorRedirectTest extends TestCase
{
    use SignatureErrorRedirectSetup;

    #[DataProvider('rejectedSignatures')]
    public function testRejectedSignatureFromTunnelFormRedirectsBackToSamePetitionWithTypedFields(
        string $failing_step,
        string $status,
    ): void {
        $this->failAt($failing_step);
        $_POST['from_tunnel'] = '1';

        $this->assertRedirectedBackToSamePetition($status);
    }

    #[DataProvider('rejectedSignatures')]
    public function testRejectedSignaturePostedToTunnelPageRedirectsBackToSamePetitionWithTypedFields(
        string $failing_step,
        string $status,
    ): void {
        $this->failAt($failing_step);
        $GLOBALS['post'] = $GLOBALS['__phpunit_clh_tunnel_page'];

        $this->assertRedirectedBackToSamePetition($status);
    }

    #[DataProvider('rejectedSignatures')]
    public function testRejectedSignatureOutsideTunnelRedirectsToPetitionPage(string $failing_step, string $status): void
    {
        $this->failAt($failing_step);

        self::assertSame(self::PETITION_URL . '?signature_status=' . $status, $this->redirectLocation());
        self::assertArrayNotHasKey('clh_signature_form', $_SESSION);
    }

    private function assertRedirectedBackToSamePetition(string $status): void
    {
        self::assertSame(
            self::TUNNEL_URL . '?signature_status=' . $status . '&clh_petition=101',
            $this->redirectLocation(),
        );
        self::assertSame(self::TYPED_FIELDS, $_SESSION['clh_signature_form'] ?? null);
    }
}
