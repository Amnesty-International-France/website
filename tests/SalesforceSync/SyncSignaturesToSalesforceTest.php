<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

// Integration test for the CLI cron job (`wp sync signatures`) that petition
// signing defers its real Salesforce sync to: sync_signatures_to_salesforce()
// in includes/salesforce/petition.php. Unlike SalesforcePetitionBulkCsvTest
// (which tests each helper function in isolation with hand-built inputs),
// this test drives the whole orchestration function end-to-end - bulk job
// creation, CSV upload, job close, polling, and result processing - through
// the same generic wp_remote_get()/wp_remote_request()/get_salesforce_data()
// stubs from tests/bootstrap.php, asserting on the full call sequence and the
// resulting local signature-status updates.
//
// get_local_user()/update_signature_status() get their own local stubs here
// (not shared in bootstrap.php) for the same reason as
// SalesforcePetitionBulkCsvTest: petitions/tables.php defines the real ones,
// and tests/Petitions/PetitionsTablesTest.php requires that file directly.
//
// poll_job_state() contains a real, unstubbable sleep(SECONDS_BETWEEN_CHECKS)
// (= sleep(30)) between each status check - it's PHP's built-in sleep(), not
// interceptable from the global namespace. This test's canned job status
// reaches "JobComplete" on the first check, so it pays that cost exactly
// once (~30s). Run in isolation via `composer run test:salesforce-sync`.

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';

if (!function_exists('get_local_user')) {
    function get_local_user(string $email): object
    {
        return (object) [
            'id' => $GLOBALS['__phpunit_local_users_by_email'][strtolower($email)]->id ?? 0,
        ];
    }
}

if (!function_exists('update_signature_status')) {
    function update_signature_status(
        $petition_id,
        $user_id,
        $pending,
        $is_synched,
        $last_sync,
        $increment_nb_try = false
    ) {
        $GLOBALS['__phpunit_updated_signature_statuses'][] = [
            'petition_id' => $petition_id,
            'user_id' => $user_id,
            'pending' => $pending,
            'is_synched' => $is_synched,
            'last_sync' => $last_sync,
            'increment_nb_try' => $increment_nb_try,
        ];
    }
}

#[Group('slow')]
final class SyncSignaturesToSalesforceTest extends TestCase
{
    private const JOB_ID = 'bulk-job-e2e-1';

    private const SALESFORCE_URL = 'https://fake-salesforce.phpunit.test/';

    protected function setUp(): void
    {
        putenv('AIF_SALESFORCE_URL=' . self::SALESFORCE_URL);

        $GLOBALS['__phpunit_updated_signature_statuses'] = [];
        $GLOBALS['__phpunit_local_users_by_email'] = [
            'ada@example.test' => (object) ['id' => 456],
            'grace@example.test' => (object) ['id' => 457],
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

        $statuses = $GLOBALS['__phpunit_updated_signature_statuses'];
        self::assertCount(4, $statuses);

        // Signatures are first marked pending as soon as the job is launched.
        self::assertSame(123, $statuses[0]['petition_id']);
        self::assertSame(456, $statuses[0]['user_id']);
        self::assertSame(1, $statuses[0]['pending']);
        self::assertSame(0, $statuses[0]['is_synched']);

        self::assertSame(124, $statuses[1]['petition_id']);
        self::assertSame(457, $statuses[1]['user_id']);
        self::assertSame(1, $statuses[1]['pending']);
        self::assertSame(0, $statuses[1]['is_synched']);

        // Once the job completes, both signatures come back in the
        // successful-results CSV and are marked synced.
        self::assertEquals(123, $statuses[2]['petition_id']);
        self::assertEquals(456, $statuses[2]['user_id']);
        self::assertSame(0, $statuses[2]['pending']);
        self::assertSame(1, $statuses[2]['is_synched']);
        self::assertTrue($statuses[2]['increment_nb_try']);

        self::assertEquals(124, $statuses[3]['petition_id']);
        self::assertEquals(457, $statuses[3]['user_id']);
        self::assertSame(0, $statuses[3]['pending']);
        self::assertSame(1, $statuses[3]['is_synched']);
        self::assertTrue($statuses[3]['increment_nb_try']);
    }
}
