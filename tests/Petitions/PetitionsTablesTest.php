<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Shared with the Salesforce tests, which exercise these real persistence
// functions through the in-memory wpdb double from tests/bootstrap.php.
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/tables.php';

final class PetitionsTablesTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
    }

    public function testGetLocalUserReturnsFalseWhenNoRowFound(): void
    {
        $GLOBALS['wpdb']->row_result = null;

        self::assertFalse(get_local_user('missing@example.test'));

        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('get_row', $call['method']);
        self::assertSame("SELECT * FROM `wp_aif_users` WHERE email = 'missing@example.test'", $call['query']);
    }

    public function testGetLocalUserReturnsTheRowWhenFound(): void
    {
        $row = (object) ['id' => 7, 'email' => 'ada@example.test'];
        $GLOBALS['wpdb']->row_result = $row;

        self::assertSame($row, get_local_user('ada@example.test'));
    }

    public function testGetLocalUserByIdSendsExpectedQuery(): void
    {
        get_local_user_by_id(7);

        self::assertSame(
            'SELECT * FROM `wp_aif_users` WHERE id = 7',
            $GLOBALS['wpdb']->calls[0]['query']
        );
    }

    public function testInsertUserReturnsTheNewInsertIdOnSuccess(): void
    {
        $GLOBALS['wpdb']->insert_result = 1;
        $GLOBALS['wpdb']->insert_id = 42;

        $result = insert_user('Mme', 'Ada', 'Lovelace', 'ada@example.test', 'France', '75001', '0102030405');

        self::assertSame(42, $result);
        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('insert', $call['method']);
        self::assertSame('wp_aif_users', $call['table']);
        self::assertSame([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'email' => 'ada@example.test',
            'civility' => 'Mme',
            'country' => 'France',
            'postal_code' => '75001',
            'phone' => '0102030405',
        ], $call['data']);
    }

    public function testInsertUserReturnsFalseWhenInsertFails(): void
    {
        $GLOBALS['wpdb']->insert_result = false;

        self::assertFalse(insert_user('Mme', 'Ada', 'Lovelace', 'ada@example.test', 'France', '75001', '0102030405'));
    }

    public function testHaveSignedIsFalseWhenNoMatchingRow(): void
    {
        $GLOBALS['wpdb']->row_result = null;

        self::assertFalse(have_signed(123, 456));

        self::assertSame(
            'SELECT * FROM `wp_aif_petitions_signatures` WHERE petition_id = 123 AND user_id = 456',
            $GLOBALS['wpdb']->calls[0]['query']
        );
    }

    public function testHaveSignedIsTrueWhenAMatchingRowExists(): void
    {
        $GLOBALS['wpdb']->row_result = (object) ['petition_id' => 123, 'user_id' => 456];

        self::assertTrue(have_signed(123, 456));
    }

    public function testInsertPetitionSignatureSendsExpectedDataAndFormat(): void
    {
        $GLOBALS['wpdb']->insert_result = 1;

        $result = insert_petition_signature(123, 456, '2026-07-01', 'WEB', 'Bravo !', 0, 1, '2026-07-02', 2);

        self::assertTrue($result);
        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame([
            'petition_id' => 123,
            'user_id' => 456,
            'date_signature' => '2026-07-01',
            'pending' => 0,
            'is_synched' => 1,
            'last_sync' => '2026-07-02',
            'nb_try' => 2,
            'code_origine' => 'WEB',
            'message' => 'Bravo !',
        ], $call['data']);
        self::assertSame(['%d', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%s'], $call['format']);
    }

    public function testInsertPetitionSignatureReturnsFalseOnFailure(): void
    {
        $GLOBALS['wpdb']->insert_result = false;

        self::assertFalse(insert_petition_signature(123, 456, '2026-07-01', 'WEB', ''));
    }

    public function testGetSignaturesToSyncQueriesPendingUnsyncedNeverTriedSignatures(): void
    {
        $GLOBALS['wpdb']->results_result = [['petition_id' => 1]];

        $result = get_signatures_to_sync();

        self::assertSame([['petition_id' => 1]], $result);
        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('get_results', $call['method']);
        self::assertStringContainsString('s.pending = 0 AND s.is_synched = 0 AND s.nb_try = 0', $call['query']);
        self::assertSame('ARRAY_A', $call['output']);
    }

    public function testGetFailedSignaturesToSyncQueriesPendingUnsyncedTriedOnceSignatures(): void
    {
        $GLOBALS['wpdb']->results_result = [];

        get_failed_signatures_to_sync();

        $call = $GLOBALS['wpdb']->calls[0];
        self::assertStringContainsString('s.pending = 0 AND s.is_synched = 0 AND s.nb_try = 1', $call['query']);
    }

    public function testUpdateSignatureStatusWithoutIncrementingNbTry(): void
    {
        update_signature_status(123, 456, 1, 0, '2026-07-01 10:00:00', false);

        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('query', $call['method']);
        self::assertSame(
            "UPDATE wp_aif_petitions_signatures SET pending = 1, is_synched = 0, last_sync = '2026-07-01 10:00:00' WHERE petition_id = 123 AND user_id = 456",
            $call['query']
        );
    }

    public function testUpdateSignatureStatusIncrementingNbTry(): void
    {
        update_signature_status(123, 456, 0, 1, '2026-07-02 09:00:00', true);

        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame(
            "UPDATE wp_aif_petitions_signatures SET pending = 0, nb_try = nb_try + 1, is_synched = 1, last_sync = '2026-07-02 09:00:00' WHERE petition_id = 123 AND user_id = 456",
            $call['query']
        );
    }
}
