<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// post_salesforce_data() is stubbed once for the whole suite in
// tests/bootstrap.php - see setUp() below for how this test seeds/reads its
// calls and canned response.

// Use the real persistence functions with the in-memory wpdb double instead
// of declaring test-only global functions that collide during discovery.
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/tables.php';

// require_once: Petitions also depends on this real file; require_once avoids
// a "cannot redeclare" fatal if both get loaded in the same PHP process.
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';

final class SalesforcePetitionBulkCsvTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
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
        $GLOBALS['wpdb']->row_result = (object) ['id' => 456];
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
        self::assertCount(2, $GLOBALS['wpdb']->calls);
        self::assertSame([
            'method' => 'get_row',
            'query' => "SELECT * FROM `wp_aif_users` WHERE email = 'Processed@Example.Test'",
        ], $GLOBALS['wpdb']->calls[0]);
        self::assertSame('query', $GLOBALS['wpdb']->calls[1]['method']);
        self::assertStringContainsString(
            'SET pending = 0, nb_try = nb_try + 1, is_synched = 1',
            $GLOBALS['wpdb']->calls[1]['query']
        );
        self::assertStringEndsWith(
            'WHERE petition_id = 123 AND user_id = 456',
            $GLOBALS['wpdb']->calls[1]['query']
        );
    }

    public function testFailedBatchMarksOnlyUnprocessedSignaturesAsRetryable(): void
    {
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

        self::assertCount(1, $GLOBALS['wpdb']->calls);
        self::assertSame('query', $GLOBALS['wpdb']->calls[0]['method']);
        self::assertStringContainsString(
            'SET pending = 0, nb_try = nb_try + 1, is_synched = 0',
            $GLOBALS['wpdb']->calls[0]['query']
        );
        self::assertStringEndsWith(
            'WHERE petition_id = 789 AND user_id = 101',
            $GLOBALS['wpdb']->calls[0]['query']
        );
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
