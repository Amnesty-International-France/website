<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

// Integration test for the CLI cron job (`wp sync signatures`) that petition
// signing defers its real Salesforce sync to: sync_signatures_to_salesforce()
// in includes/salesforce/petition.php. Unlike SalesforcePetitionBulkCsvTest
// (unit-level, hand-built inputs), this drives the whole orchestration
// end-to-end - bulk job creation, CSV upload, job close, polling, result
// processing - through the generic wp_remote_*()/get_salesforce_data() stubs.
//
// poll_job_state() contains a real, unstubbable sleep(30) between status
// checks; this test's canned job status reaches "JobComplete" on the first
// check, so it pays that cost exactly once. Run it directly via
// `composer run test:salesforce-sync`.

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/tables.php';
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';

#[Group('slow')]
final class SyncSignaturesToSalesforceTest extends TestCase
{
    private const JOB_ID = 'bulk-job-e2e-1';

    private const SALESFORCE_URL = 'https://fake-salesforce.phpunit.test/';

    protected function setUp(): void
    {
        putenv('AIF_SALESFORCE_URL=' . self::SALESFORCE_URL);

        $GLOBALS['wpdb'] = new wpdb();
        $GLOBALS['wpdb']->row_results = [
            (object) ['id' => 456],
            (object) ['id' => 457],
        ];
        $GLOBALS['__phpunit_acf_field_values'] = [
            123 => ['uidsf' => 'UID-123'],
            124 => ['uidsf' => 'UID-124'],
        ];

        $GLOBALS['__phpunit_salesforce_data_calls'] = [];
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [
            // create_bulk_job_signatures() -> post_salesforce_data()
            [
                'id' => self::JOB_ID,
                'contentUrl' => 'services/data/v57.0/jobs/ingest/' . self::JOB_ID . '/batches',
            ],
            // close_bulk_job() -> patch_salesforce_data()
            ['id' => self::JOB_ID, 'state' => 'UploadComplete'],
        ];

        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [
            // upload_bulk_data() -> wp_remote_request() (PUT)
            ['body' => '', 'response' => ['code' => 201]],
            // poll_job_state() -> wp_remote_get(), terminal on the first check
            [
                'body' => json_encode([
                    'id' => self::JOB_ID,
                    'state' => 'JobComplete',
                    'numberRecordsProcessed' => 2,
                    'numberRecordsFailed' => 0,
                ]),
                'response' => ['code' => 200],
            ],
            // get_bulk_success_results() -> wp_remote_get()
            [
                'body' => "Ext_ID_WP__c,Email__c\n123,ada@example.test\n124,grace@example.test\n",
                'response' => ['code' => 200],
            ],
            // get_bulk_failed_results() -> wp_remote_get() (no failures)
            ['body' => 'Ext_ID_WP__c,Email__c', 'response' => ['code' => 200]],
            // get_bulk_unprocessed_results() -> wp_remote_get() (nothing left unprocessed)
            ['body' => 'Ext_ID_WP__c,Email__c', 'response' => ['code' => 200]],
        ];
    }

    public function testSyncSignaturesToSalesforceRunsFullBulkJobAndUpdatesLocalStatuses(): void
    {
        $signatures = [
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
                'message' => '',
            ],
            [
                'petition_id' => 124,
                'user_id' => 457,
                'is_synched' => 0,
                'last_sync' => null,
                'civility' => 'M',
                'firstname' => 'Grace',
                'lastname' => 'Hopper',
                'email' => 'grace@example.test',
                'date_signature' => '2026-06-22',
                'country' => 'France',
                'postal_code' => '75002',
                'phone' => '0102030406',
                'code_origine' => 'WEB',
                'message' => '',
            ],
        ];

        sync_signatures_to_salesforce($signatures);

        $data_calls = $GLOBALS['__phpunit_salesforce_data_calls'];
        self::assertCount(2, $data_calls);

        self::assertSame('POST', $data_calls[0]['method']);
        self::assertSame('services/data/v57.0/jobs/ingest', $data_calls[0]['url']);
        self::assertSame('Signature_de_petition__c', $data_calls[0]['params']['object']);
        self::assertSame('insert', $data_calls[0]['params']['operation']);
        self::assertSame('CSV', $data_calls[0]['params']['contentType']);
        self::assertSame('CRLF', $data_calls[0]['params']['lineEnding']);

        self::assertSame('PATCH', $data_calls[1]['method']);
        self::assertSame('services/data/v57.0/jobs/ingest/' . self::JOB_ID, $data_calls[1]['url']);
        self::assertSame('UploadComplete', $data_calls[1]['params']['state']);

        $remote_calls = $GLOBALS['__phpunit_wp_remote_calls'];
        self::assertCount(5, $remote_calls);

        [$upload_call, $poll_call, $success_call, $failed_call, $unprocessed_call] = $remote_calls;

        self::assertSame('PUT', $upload_call['method']);
        self::assertSame(
            self::SALESFORCE_URL . 'services/data/v57.0/jobs/ingest/' . self::JOB_ID . '/batches',
            $upload_call['url']
        );
        self::assertSame('text/csv', $upload_call['args']['headers']['Content-Type']);
        self::assertSame('Bearer fake-e2e-access-token', $upload_call['args']['headers']['Authorization']);
        self::assertStringContainsString('Ada', $upload_call['args']['body']);
        self::assertStringContainsString('ada@example.test', $upload_call['args']['body']);
        self::assertStringContainsString('UID-123', $upload_call['args']['body']);

        self::assertSame('GET', $poll_call['method']);
        self::assertSame(
            self::SALESFORCE_URL . 'services/data/v57.0/jobs/ingest/' . self::JOB_ID,
            $poll_call['url']
        );

        self::assertStringEndsWith('/successfulResults', $success_call['url']);
        self::assertStringEndsWith('/failedResults', $failed_call['url']);
        self::assertStringEndsWith('/unprocessedrecords', $unprocessed_call['url']);

        $database_calls = $GLOBALS['wpdb']->calls;
        self::assertCount(6, $database_calls);

        [$pending_ada, $pending_grace, $find_ada, $sync_ada, $find_grace, $sync_grace] = $database_calls;

        // Signatures are first marked pending as soon as the job is launched.
        self::assertSame('query', $pending_ada['method']);
        self::assertStringContainsString('SET pending = 1, is_synched = 0', $pending_ada['query']);
        self::assertStringEndsWith('WHERE petition_id = 123 AND user_id = 456', $pending_ada['query']);

        self::assertSame('query', $pending_grace['method']);
        self::assertStringContainsString('SET pending = 1, is_synched = 0', $pending_grace['query']);
        self::assertStringEndsWith('WHERE petition_id = 124 AND user_id = 457', $pending_grace['query']);

        // Once the job completes, both signatures come back in the
        // successful-results CSV and are marked synced.
        self::assertSame([
            'method' => 'get_row',
            'query' => "SELECT * FROM `wp_aif_users` WHERE email = 'ada@example.test'",
        ], $find_ada);
        self::assertSame('query', $sync_ada['method']);
        self::assertStringContainsString(
            'SET pending = 0, nb_try = nb_try + 1, is_synched = 1',
            $sync_ada['query']
        );
        self::assertStringEndsWith('WHERE petition_id = 123 AND user_id = 456', $sync_ada['query']);

        self::assertSame([
            'method' => 'get_row',
            'query' => "SELECT * FROM `wp_aif_users` WHERE email = 'grace@example.test'",
        ], $find_grace);
        self::assertSame('query', $sync_grace['method']);
        self::assertStringContainsString(
            'SET pending = 0, nb_try = nb_try + 1, is_synched = 1',
            $sync_grace['query']
        );
        self::assertStringEndsWith('WHERE petition_id = 124 AND user_id = 457', $sync_grace['query']);
    }
}
