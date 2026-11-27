<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/bank/iban-request.php';

/**
 * Demande de changement d'IBAN : validation de l'IBAN, demande ouverte dans
 * Salesforce et verrou atomique contre les envois simultanés.
 */
final class IbanRequestTest extends TestCase
{
    private const CONTACT_ID = '003000000000001';

    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
    }

    public static function ibans(): iterable
    {
        yield 'French IBAN' => ['FR7630006000011234567890189', true];
        yield 'French IBAN with letters in the BBAN' => ['FR1420041010050500013M02606', true];
        yield 'German IBAN' => ['DE89370400440532013000', true];
        yield 'wrong check digits' => ['FR7630006000011234567890188', false];
        yield 'truncated' => ['FR763000600001', false];
        yield 'lowercase, not normalized' => ['fr7630006000011234567890189', false];
        yield 'not an IBAN' => ['ABC', false];
        yield 'empty' => ['', false];
    }

    #[DataProvider('ibans')]
    public function testValidatesTheFormatAndCheckDigits(string $iban, bool $expected): void
    {
        self::assertSame($expected, aif_is_valid_iban($iban));
    }

    public static function demands(): iterable
    {
        yield 'no request' => [[], false];
        yield 'IBAN change in progress' => [[self::demand('Changement IBAN', 'En cours de traitement')], true];
        yield 'IBAN change with an unknown status' => [[self::demand('Changement IBAN', 'Nouveau')], false];
        yield 'IBAN change done' => [[self::demand('Changement IBAN', 'Traité')], false];
        yield 'IBAN change rejected' => [[self::demand('Changement IBAN', 'Rejeté')], false];
        yield 'IBAN change failed' => [[self::demand('Changement IBAN', 'Fermé - Echoué')], false];
        yield 'another request in progress' => [[self::demand('Envoi duplicata', 'En cours de traitement')], false];
        yield 'request without a type' => [[(object) ['Subject' => 'Reçu fiscal', 'Statut_Espace_Don__c' => 'En cours de traitement']], false];
    }

    #[DataProvider('demands')]
    public function testFindsAnOpenIbanChangeAmongTheSalesforceRequests(array $demands, bool $expected): void
    {
        self::assertSame($expected, aif_has_open_iban_request($demands));
    }

    public function testCountsTheIbanRequestsWhateverTheirStatus(): void
    {
        $demands = [
            self::demand('Changement IBAN', 'Traité'),
            self::demand('Envoi duplicata', 'En cours de traitement'),
            self::demand('Changement IBAN', 'En cours de traitement'),
        ];

        self::assertSame(2, aif_count_iban_requests($demands));
    }

    public function testClaimsTheLockWhenItIsFree(): void
    {
        $GLOBALS['wpdb']->query_results = [2];

        self::assertTrue(aif_claim_iban_request_lock(self::CONTACT_ID, 3));
        self::assertStringStartsWith('INSERT IGNORE', $GLOBALS['wpdb']->calls[0]['query']);
        self::assertStringContainsString("'_transient_" . aif_iban_request_lock_key(self::CONTACT_ID) . "', '3'", $GLOBALS['wpdb']->calls[0]['query']);
    }

    public function testClaimsTheLockWhenOnlyItsExpiryWasMissing(): void
    {
        // A leftover value row without its expiry: only our expiry row was inserted.
        $GLOBALS['wpdb']->query_results = [1, 1];
        $GLOBALS['wpdb']->var_result = (string) (time() + AIF_IBAN_REQUEST_LOCK_TTL);

        self::assertTrue(aif_claim_iban_request_lock(self::CONTACT_ID, 3));
        self::assertStringStartsWith('UPDATE wp_options SET option_value = \'3\'', self::queries()[1]);
    }

    public function testDoesNotClaimALockHeldByAnotherRequest(): void
    {
        $GLOBALS['wpdb']->query_results = [0];
        $GLOBALS['wpdb']->var_result = (string) (time() + 600);

        self::assertFalse(aif_claim_iban_request_lock(self::CONTACT_ID, 0));
        self::assertCount(1, self::queries());
    }

    public static function expiredLockRetakes(): iterable
    {
        yield 'this request wins the compare-and-swap' => [1, true];
        yield 'another request retook it first' => [0, false];
    }

    #[DataProvider('expiredLockRetakes')]
    public function testRetakesAnExpiredLockOnlyOnce(int $updated, bool $expected): void
    {
        $expired = (string) (time() - 1);
        $GLOBALS['wpdb']->query_results = [0, $updated];
        $GLOBALS['wpdb']->var_result = $expired;

        $GLOBALS['wpdb']->query_results = [0, $updated, 1];

        self::assertSame($expected, aif_claim_iban_request_lock(self::CONTACT_ID, 0));
        self::assertStringContainsString("AND option_value = '{$expired}'", self::queries()[1]);
    }

    public function testDoesNotClaimTheLockWhenTheDatabaseFails(): void
    {
        $GLOBALS['wpdb']->query_results = [false];

        self::assertFalse(aif_claim_iban_request_lock(self::CONTACT_ID, 0));
    }

    public static function lockExpiries(): iterable
    {
        yield 'no lock' => [null, false];
        yield 'lock not expired' => [600, true];
        yield 'lock expired' => [-1, false];
    }

    #[DataProvider('lockExpiries')]
    public function testTellsWhetherTheLockIsHeld(?int $expiresIn, bool $expected): void
    {
        $GLOBALS['wpdb']->var_result = null === $expiresIn ? null : (string) (time() + $expiresIn);

        self::assertSame($expected, aif_is_iban_request_locked(self::CONTACT_ID));
    }

    public function testReleaseDeletesTheLockAndItsExpiry(): void
    {
        aif_release_iban_request_lock(self::CONTACT_ID);

        $key = aif_iban_request_lock_key(self::CONTACT_ID);
        self::assertSame(
            "DELETE FROM wp_options WHERE option_name IN ('_transient_{$key}', '_transient_timeout_{$key}')",
            self::queries()[0]
        );
    }

    /** @return list<string> */
    private static function queries(): array
    {
        return array_values(array_map(
            static fn (array $call): string => $call['query'],
            array_filter($GLOBALS['wpdb']->calls, static fn (array $call): bool => 'query' === $call['method'])
        ));
    }

    private static function demand(string $type, string $status): object
    {
        return (object) ['Type_de_demande_AIF__c' => $type, 'Statut_Espace_Don__c' => $status];
    }
}
