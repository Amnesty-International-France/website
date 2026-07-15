<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

$updated_signature_statuses = [];
$local_users = [];

// post_salesforce_data() is stubbed once for the whole suite in tests/bootstrap.php
// (shared across domains that call it, e.g. petitions and users) - see setUp()
// below for how this test seeds/reads its calls and canned response.

function update_signature_status($petition_id, $user_id, $pending, $is_synched, $last_sync, $increment_nb_try = false): void
{
    global $updated_signature_statuses;

    $updated_signature_statuses[] = compact('petition_id', 'user_id', 'pending', 'is_synched', 'last_sync', 'increment_nb_try');
}

function get_local_user(string $email): object
{
    global $local_users;

    return (object) [
        'id' => $local_users[strtolower($email)] ?? 0,
    ];
}

// require_once: other testsuites (e.g. Petitions) also depend on this real
// file for its thin Salesforce-petition-object wrappers; require_once avoids
// a "cannot redeclare" fatal if both get loaded in the same PHP process.
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';

final class SalesforcePetitionBulkCsvTest extends TestCase
{
    protected function setUp(): void
    {
        global $updated_signature_statuses, $local_users;

        $updated_signature_statuses = [];
        $local_users = [
            'processed@example.test' => 456,
        ];
        $GLOBALS['__phpunit_acf_field_values'] = [
            123 => ['uidsf' => 'UID-123'],
            789 => ['uidsf' => 'UID-789'],
        ];
        $GLOBALS['__phpunit_salesforce_data_calls'] = [];
        $GLOBALS['__phpunit_salesforce_data_response'] = [
            'id' => '750-job-id',
            'contentUrl' => 'services/data/v57.0/jobs/ingest/750-job-id/batches',
        ];
    }

    public function testBulkCsvPreservesMessagesWithNewlinesCommasAndQuotes(): void
    {
        $message = "Ligne 1\nElle a dit \"bonjour\", puis merci.";
        $csv = prepare_bulk_data([
            [
                'petition_id' => 123,
                'user_id' => 456,
                'is_synched' => 0,
                'last_sync' => null,
                'civility' => 'Mme',
                'firstname' => 'Ada',
                'lastname' => 'Lovelace',
                'email' => 'ada@example.test',
                'date_signature' => '2026-06-22',
                'country' => 'France',
                'postal_code' => '75001',
                'phone' => '0102030405',
                'code_origine' => 'WEB',
                'message' => $message,
            ],
        ]);

        self::assertStringContainsString("\r\n", $csv);
        self::assertStringEndsWith("\r\n", $csv);
        self::assertStringNotContainsString('\\\"', $csv);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $header = fgetcsv($stream, escape: '');
        $row = fgetcsv($stream, escape: '');
        $extra_row = fgetcsv($stream, escape: '');
        fclose($stream);

        self::assertIsArray($header);
        self::assertIsArray($row);
        self::assertFalse($extra_row);
        self::assertCount(14, $header);
        self::assertCount(count($header), $row);
        self::assertSame($message, $row[13]);
    }

    public function testBulkIngestJobDeclaresCsvContentAndLineEndings(): void
    {
        create_bulk_job_signatures();

        $call = $GLOBALS['__phpunit_salesforce_data_calls'][0];

        self::assertSame('services/data/v57.0/jobs/ingest', $call['url']);
        self::assertSame('CSV', $call['params']['contentType']);
        self::assertSame('CRLF', $call['params']['lineEnding']);
    }

    public function testBulkResultRowsUpdateLocalSignatureAndReturnProcessedKeys(): void
    {
        global $updated_signature_statuses;

        $processed_signatures = [];

        process_bulk_result_rows(
            [
                [
                    'Ext_ID_WP__c' => 123,
                    'Email__c' => 'Processed@Example.Test',
                ],
            ],
            0,
            1,
            true,
            $processed_signatures
        );

        self::assertSame([get_signature_sync_key(123, 'processed@example.test')], $processed_signatures);
        self::assertCount(1, $updated_signature_statuses);
        self::assertSame(123, $updated_signature_statuses[0]['petition_id']);
        self::assertSame(456, $updated_signature_statuses[0]['user_id']);
        self::assertSame(0, $updated_signature_statuses[0]['pending']);
        self::assertSame(1, $updated_signature_statuses[0]['is_synched']);
        self::assertTrue($updated_signature_statuses[0]['increment_nb_try']);
    }

    public function testFailedBatchMarksOnlyUnprocessedSignaturesAsRetryable(): void
    {
        global $updated_signature_statuses;

        mark_unprocessed_signatures_batch_as_failed(
            [
                [
                    'petition_id' => 123,
                    'user_id' => 456,
                    'email' => 'processed@example.test',
                ],
                [
                    'petition_id' => 789,
                    'user_id' => 101,
                    'email' => 'retry@example.test',
                ],
            ],
            [
                get_signature_sync_key(123, 'processed@example.test'),
            ]
        );

        self::assertCount(1, $updated_signature_statuses);
        self::assertSame(789, $updated_signature_statuses[0]['petition_id']);
        self::assertSame(101, $updated_signature_statuses[0]['user_id']);
        self::assertSame(0, $updated_signature_statuses[0]['pending']);
        self::assertSame(0, $updated_signature_statuses[0]['is_synched']);
        self::assertTrue($updated_signature_statuses[0]['increment_nb_try']);
    }

    public function testBulkJobHasNoProcessedRecordsOnlyWhenSalesforceCountersAreEmpty(): void
    {
        self::assertTrue(bulk_job_has_no_processed_records([
            'numberRecordsProcessed' => 0,
            'numberRecordsFailed' => 0,
        ]));

        self::assertFalse(bulk_job_has_no_processed_records([
            'numberRecordsProcessed' => 1,
            'numberRecordsFailed' => 0,
        ]));

        self::assertFalse(bulk_job_has_no_processed_records([
            'numberRecordsProcessed' => 0,
            'numberRecordsFailed' => 1,
        ]));
    }
}
