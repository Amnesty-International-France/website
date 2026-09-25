<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/create-petition.php';
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/syncs.php';

final class CreateMissingPetitionsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__phpunit_posts'] = [];
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $GLOBALS['__phpunit_post_terms'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [];
        $GLOBALS['__phpunit_get_posts_calls'] = [];
        $GLOBALS['__phpunit_get_posts_result'] = [];
        $GLOBALS['__phpunit_salesforce_data_calls'] = [];
        $GLOBALS['__phpunit_salesforce_data_response'] = false;
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [];
        $GLOBALS['__phpunit_wp_cli_messages'] = [];
    }

    /**
     * Seeds a published petition with no Salesforce ID, returned by the
     * get_posts() query of the command.
     */
    private static function unlinkedPetition(int $post_id): void
    {
        $GLOBALS['__phpunit_posts'][$post_id] = (object) [
            'ID' => $post_id,
            'post_type' => 'petition',
            'post_status' => 'publish',
            'post_title' => "Pétition {$post_id}",
            'post_excerpt' => '',
            'post_date' => '2026-09-10 08:00:00',
        ];
        $GLOBALS['__phpunit_acf_field_values'][$post_id] = [
            'date_de_fin' => '2026-12-31',
            'type' => ['value' => 'petition'],
        ];
        $GLOBALS['__phpunit_get_posts_result'][] = $post_id;
    }

    private static function runCommand(array $args = [], array $assoc_args = []): void
    {
        (new Sync_Command())->create_missing_petitions($args, $assoc_args);
    }

    /** @return list<string> */
    private static function messages(string $type): array
    {
        $messages = array_filter($GLOBALS['__phpunit_wp_cli_messages'], fn (array $message) => $message[0] === $type);

        return array_values(array_column($messages, 1));
    }

    public function testRefusesToTargetEveryPublishedPetition(): void
    {
        self::runCommand();

        self::assertSame(['Pass petition IDs or --since=<date>'], self::messages('error'));
        self::assertSame([], $GLOBALS['__phpunit_get_posts_calls']);
    }

    #[DataProvider('invalidSinceProvider')]
    public function testRejectsAnInvalidSinceDate(string $since): void
    {
        self::runCommand([], ['since' => $since]);

        self::assertCount(1, self::messages('error'));
        self::assertSame([], $GLOBALS['__phpunit_get_posts_calls']);
    }

    public static function invalidSinceProvider(): iterable
    {
        yield 'french format' => ['01/07/2026'];
        yield 'non-existent day' => ['2026-02-30'];
        yield 'relative date' => ['yesterday'];
    }

    public function testOnlyQueriesPublishedPetitionsWithoutSalesforceId(): void
    {
        self::runCommand(['159460', '42'], ['since' => '2026-07-01']);

        $query = $GLOBALS['__phpunit_get_posts_calls'][0];
        self::assertSame('petition', $query['post_type']);
        self::assertSame('publish', $query['post_status']);
        self::assertSame(-1, $query['posts_per_page']);
        self::assertSame([159460, 42], $query['post__in']);
        self::assertSame([
            'relation' => 'OR',
            ['key' => 'uidsf', 'compare' => 'NOT EXISTS'],
            ['key' => 'uidsf', 'value' => ''],
        ], $query['meta_query']);
        self::assertSame([['after' => '2026-07-01', 'inclusive' => true]], $query['date_query']);
    }

    public function testDryRunListsPetitionsWithoutCallingSalesforce(): void
    {
        self::unlinkedPetition(159460);

        self::runCommand([], ['since' => '2026-07-01', 'dry-run' => true]);

        self::assertSame([], $GLOBALS['__phpunit_salesforce_data_calls']);
        self::assertSame(['#159460 "Pétition 159460" (published 2026-09-10 08:00:00): to create'], self::messages('log'));
        self::assertSame(['1 petition(s) to create'], self::messages('success'));
    }

    public function testCreatesEachPetitionInSalesforce(): void
    {
        self::unlinkedPetition(159460);
        self::unlinkedPetition(159461);
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [
            ['success' => true, 'id' => 'sf-1'],
            ['Ext_ID_Petition__c' => 'ext-1', 'Code_defaut__c' => 'CODE-1'],
            ['success' => true, 'id' => 'sf-2'],
            ['Ext_ID_Petition__c' => 'ext-2', 'Code_defaut__c' => 'CODE-2'],
        ];

        self::runCommand([], ['since' => '2026-07-01']);

        self::assertSame('ext-1', $GLOBALS['__phpunit_acf_field_values'][159460]['uidsf']);
        self::assertSame('CODE-1', $GLOBALS['__phpunit_acf_field_values'][159460]['code_origine']);
        self::assertSame('ext-2', $GLOBALS['__phpunit_acf_field_values'][159461]['uidsf']);
        self::assertSame('CODE-2', $GLOBALS['__phpunit_acf_field_values'][159461]['code_origine']);
        self::assertSame(['2 petition(s) created'], self::messages('success'));
        self::assertSame([], self::messages('error'));
    }

    public function testSkipsAPetitionWhoseSalesforceRecordAlreadyExists(): void
    {
        self::unlinkedPetition(159460);
        $GLOBALS['__phpunit_acf_field_values'][159460]['sfid'] = 'sf-existing';

        self::runCommand(['159460']);

        self::assertSame([], $GLOBALS['__phpunit_salesforce_data_calls']);
        self::assertStringContainsString('sf-existing', self::messages('warning')[0]);
        self::assertSame(['1 petition(s) need a manual check in Salesforce'], self::messages('error'));
    }

    public function testReportsAPetitionThatCouldNotBeLinkedToSalesforce(): void
    {
        self::unlinkedPetition(159460);
        $GLOBALS['__phpunit_salesforce_data_response'] = ['success' => false];

        self::runCommand(['159460']);

        self::assertArrayNotHasKey('uidsf', $GLOBALS['__phpunit_acf_field_values'][159460]);
        self::assertSame(['#159460 "Pétition 159460" (published 2026-09-10 08:00:00): could not be linked to Salesforce'], self::messages('warning'));
        self::assertSame(['1 petition(s) need a manual check in Salesforce'], self::messages('error'));
    }
}
