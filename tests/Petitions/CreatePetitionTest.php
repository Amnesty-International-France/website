<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// require_once avoids "cannot redeclare" collisions with the other suites that
// require these same files (SalesforcePetitionBulkCsvTest,
// CreateMissingPetitionsCommandTest).
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/petition.php';
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/create-petition.php';

final class CreatePetitionTest extends TestCase
{
    private const POST_ID = 555;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_posts'] = [];
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $GLOBALS['__phpunit_post_terms'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [];
        $GLOBALS['__phpunit_salesforce_data_calls'] = [];
        // false => "no usable Salesforce response"; enough for tests that
        // only care about the payload sent (still recorded in ->calls).
        $GLOBALS['__phpunit_salesforce_data_response'] = false;
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [];
    }

    private static function publishedPetitionPost(string $title = 'Justice pour...', string $excerpt = 'Un résumé.'): void
    {
        $GLOBALS['__phpunit_posts'][self::POST_ID] = (object) [
            'ID' => self::POST_ID,
            'post_type' => 'petition',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_excerpt' => $excerpt,
        ];
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID] = [
            'date_de_fin' => '2026-12-31',
            'type' => ['value' => 'petition'],
        ];
    }

    /** @return array<int,array{method:string,url:string,params?:array}> */
    private static function salesforceCalls(): array
    {
        return $GLOBALS['__phpunit_salesforce_data_calls'];
    }

    public function testDoesNothingWhenPostTypeIsNotPetition(): void
    {
        $GLOBALS['__phpunit_posts'][self::POST_ID] = (object) [
            'ID' => self::POST_ID,
            'post_type' => 'page',
            'post_status' => 'publish',
        ];

        create_petition(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public function testDoesNothingWhenPostDoesNotExist(): void
    {
        create_petition(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    #[DataProvider('unpublishedStatusProvider')]
    public function testDoesNothingWhenPostIsNotPublished(string $status): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_posts'][self::POST_ID]->post_status = $status;

        create_petition(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public static function unpublishedStatusProvider(): iterable
    {
        yield 'draft' => ['draft'];
        yield 'still scheduled' => ['future'];
    }

    public function testCreatesAScheduledPetitionOnceWpCronPublishesIt(): void
    {
        // Scheduling it saves the ACF form while the post is still "future"...
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_posts'][self::POST_ID]->post_status = 'future';
        create_petition(self::POST_ID);
        self::assertSame([], self::salesforceCalls());

        // ...then WP-Cron publishes it through wp_publish_post(), with no ACF save.
        $GLOBALS['__phpunit_posts'][self::POST_ID]->post_status = 'publish';
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [
            ['success' => true, 'id' => 'sf-000123'],
            ['Ext_ID_Petition__c' => 'ext-999', 'Code_defaut__c' => 'WEB'],
        ];
        foreach ($GLOBALS['__phpunit_registered_actions']['publish_future_post'] ?? [] as $callback) {
            $callback(self::POST_ID);
        }

        self::assertSame('sf-000123', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['sfid'] ?? null);
        self::assertSame('ext-999', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf'] ?? null);
        self::assertSame('WEB', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['code_origine'] ?? null);
    }

    public function testDoesNothingWhenAlreadySyncedToSalesforce(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf'] = 'already-synced';

        create_petition(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public function testPostsExpectedPayloadToSalesforce(): void
    {
        self::publishedPetitionPost('Justice pour Ada', 'Un résumé court.');
        $GLOBALS['__phpunit_post_terms'][self::POST_ID]['combat'] = [(object) ['name' => 'Droits des femmes']];
        $GLOBALS['__phpunit_post_permalinks'][self::POST_ID] = 'https://example.test/petitions/justice-pour-ada';

        create_petition(self::POST_ID);

        $calls = self::salesforceCalls();
        self::assertCount(1, $calls);
        self::assertSame('POST', $calls[0]['method']);
        self::assertSame('services/data/v57.0/sobjects/Petition__c/', $calls[0]['url']);

        $payload = $calls[0]['params'];
        self::assertSame('Justice pour Ada', $payload['Name']);
        self::assertSame('0121o000000kdzmAAA', $payload['RecordTypeId']);
        self::assertSame('2026-12-31', $payload['Date_de_cloture__c']);
        self::assertSame('Un résumé court.', $payload['Description_de_la_petition__c']);
        self::assertSame('https://example.test/petitions/justice-pour-ada', $payload['Lien_petition__c']);
        self::assertSame('Pétition', $payload['Type_action__c']);
        self::assertSame('Droits des femmes', $payload['Combats__c']);
    }

    public function testCreatesThePetitionInSalesforceAndStoresReturnedIdsOnFullSuccess(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_salesforce_data_response_queue'] = [
            ['success' => true, 'id' => 'sf-000123'],
            ['Ext_ID_Petition__c' => 'ext-999', 'Code_defaut__c' => 'WEB'],
        ];

        create_petition(self::POST_ID);

        self::assertSame('sf-000123', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['sfid']);
        self::assertSame('ext-999', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf']);
        self::assertSame('WEB', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['code_origine']);
    }

    public function testDoesNotStoreIdsWhenSalesforceCreationFails(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_salesforce_data_response'] = ['success' => false];

        create_petition(self::POST_ID);

        self::assertArrayNotHasKey('sfid', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]);
        self::assertArrayNotHasKey('uidsf', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]);
    }

    public function testTruncatesLongTitlesTo80CharsExactly(): void
    {
        // 85 identical chars, no whitespace to trim once cut at 80.
        self::publishedPetitionPost(str_repeat('a', 85));

        create_petition(self::POST_ID);

        self::assertSame(str_repeat('a', 80), self::salesforceCalls()[0]['params']['Name']);
    }

    public function testRtrimsTrailingWhitespaceLeftByTruncation(): void
    {
        // Char 80 (0-indexed 79) lands on a space once cut - rtrim removes it.
        self::publishedPetitionPost(str_repeat('a', 79) . '  reste du titre');

        create_petition(self::POST_ID);

        self::assertSame(str_repeat('a', 79), self::salesforceCalls()[0]['params']['Name']);
    }

    public function testTypeActionFallsBackToActionDeSoutienWhenTypeIsNotPetition(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['type'] = ['value' => 'action-de-soutien'];

        create_petition(self::POST_ID);

        self::assertSame('Action de soutien', self::salesforceCalls()[0]['params']['Type_action__c']);
    }

    public function testCombatsIsEmptyStringWhenThereAreNoTerms(): void
    {
        self::publishedPetitionPost();
        // No terms seeded - the get_the_terms() stub resolves to false.

        create_petition(self::POST_ID);

        self::assertSame('', self::salesforceCalls()[0]['params']['Combats__c']);
    }

    public function testCombatsIsEmptyStringWhenTermsLookupIsAWpError(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_post_terms'][self::POST_ID]['combat'] = new WP_Error('bad', 'oops');

        create_petition(self::POST_ID);

        self::assertSame('', self::salesforceCalls()[0]['params']['Combats__c']);
    }

    /**
     * Mimics ACF saving the editor's form on acf/save_post (priority 10):
     * each value goes through the acf/update_value/name={name} filters.
     *
     * @param array<string,mixed> $values
     */
    private static function saveAcfForm(array $values): void
    {
        foreach ($values as $name => $value) {
            foreach ($GLOBALS['__phpunit_registered_filters']["acf/update_value/name={$name}"] ?? [] as $callback) {
                $value = $callback($value, self::POST_ID, ['name' => $name]);
            }
            update_field($name, $value, self::POST_ID);
        }
    }

    public function testSavingAStaleEditorFormDoesNotCreateThePetitionAgain(): void
    {
        // Linked when published (or by WP-Cron), while the editor still shows
        // the empty values it was loaded with.
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID] += [
            'sfid' => 'sf-000123',
            'uidsf' => 'ext-999',
            'code_origine' => 'WEB',
        ];

        self::saveAcfForm(['uidsf' => '', 'code_origine' => '']);
        create_petition(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
        self::assertSame('ext-999', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf']);
        self::assertSame('WEB', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['code_origine']);
    }

    public function testASalesforceLinkTypedInTheEditorReplacesTheStoredOne(): void
    {
        // e.g. the petition was created by hand in Salesforce.
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID] += ['uidsf' => 'ext-999', 'code_origine' => 'WEB'];

        self::saveAcfForm(['uidsf' => 'ext-manual', 'code_origine' => 'MANUAL']);

        self::assertSame('ext-manual', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf']);
        self::assertSame('MANUAL', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['code_origine']);
    }

    public function testAnEmptySalesforceLinkStaysEmptyUntilThePetitionIsCreated(): void
    {
        self::publishedPetitionPost();

        self::saveAcfForm(['uidsf' => '', 'code_origine' => '']);

        self::assertSame('', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf']);
        self::assertSame('', $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['code_origine']);
    }

    public function testUpdatePetitionEndDateDoesNothingWhenPostTypeIsNotPetition(): void
    {
        $GLOBALS['__phpunit_posts'][self::POST_ID] = (object) [
            'ID' => self::POST_ID,
            'post_type' => 'page',
        ];

        update_petition_end_date(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public function testUpdatePetitionEndDateDoesNothingWhenDateIsEmpty(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['date_de_fin'] = '';

        update_petition_end_date(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public function testUpdatePetitionEndDateDoesNothingWhenPetitionNotYetSyncedToSalesforce(): void
    {
        self::publishedPetitionPost();
        // No 'uidsf' seeded - get_field() resolves to '' (falsy).

        update_petition_end_date(self::POST_ID);

        self::assertSame([], self::salesforceCalls());
    }

    public function testUpdatePetitionEndDatePatchesSalesforceWhenAlreadySynced(): void
    {
        self::publishedPetitionPost();
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['uidsf'] = 'ext-42';
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['date_de_fin'] = '2027-01-15';

        update_petition_end_date(self::POST_ID);

        $calls = self::salesforceCalls();
        self::assertSame('PATCH', $calls[0]['method']);
        self::assertSame('services/data/v57.0/sobjects/Petition__c/Ext_ID_Petition__c/ext-42', $calls[0]['url']);
        self::assertSame(['Date_de_cloture__c' => '2027-01-15'], $calls[0]['params']);
    }
}
