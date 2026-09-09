<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Only insert_users_records() and get_petition_id_from_uidsf() are exercised
// here - the Sync_Command class methods (import_signatures, compteurs, ...)
// need a WP_Query fake and several more Salesforce/table dependencies; out of
// scope for this pass since we never invoke them (requiring this file just
// defines the class and these two standalone functions).
require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/syncs.php';

final class SyncsHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
        $GLOBALS['__phpunit_get_posts_calls'] = [];
        $GLOBALS['__phpunit_get_posts_result'] = [];
    }

    public function testInsertUsersRecordsDoesNothingWhenResponseHasNoRecordsKey(): void
    {
        insert_users_records(['nextRecordsUrl' => '/some/url']);

        self::assertSame([], $GLOBALS['wpdb']->calls);
    }

    public function testInsertUsersRecordsInsertsANewLocalUser(): void
    {
        $GLOBALS['wpdb']->var_result = null;
        $GLOBALS['wpdb']->insert_result = 1;

        insert_users_records([
            'records' => [[
                'Salutation' => 'Mme',
                'FirstName' => 'Ada',
                'LastName' => 'Lovelace',
                'Email' => 'ada@example.test',
                'Code_Postal__c' => '75001',
                'Pays__c' => 'France',
                'MobilePhone' => '0102030405',
            ]],
        ]);

        $calls = $GLOBALS['wpdb']->calls;
        self::assertSame('get_var', $calls[0]['method']);
        self::assertSame('insert', $calls[1]['method']);
        self::assertSame([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'email' => 'ada@example.test',
            'civility' => 'Mme',
            'country' => 'France',
            'postal_code' => '75001',
            'phone' => '0102030405',
        ], $calls[1]['data']);
        self::assertCount(7, $calls[1]['format']);
    }

    public function testInsertUsersRecordsUpdatesAnExistingLocalUserWithoutTouchingItsEmail(): void
    {
        $GLOBALS['wpdb']->var_result = 42;
        $GLOBALS['wpdb']->update_result = 1;

        insert_users_records([
            'records' => [[
                'Salutation' => 'Mme',
                'FirstName' => 'Ada',
                'LastName' => 'Lovelace',
                'Email' => 'ada@example.test',
                'Code_Postal__c' => '75001',
                'Pays__c' => 'France',
                'MobilePhone' => '0102030405',
            ]],
        ]);

        $calls = $GLOBALS['wpdb']->calls;
        self::assertSame('update', $calls[1]['method']);
        self::assertArrayNotHasKey('email', $calls[1]['data']);
        self::assertSame(['email' => 'ada@example.test'], $calls[1]['where']);
        self::assertCount(6, $calls[1]['format']);
    }

    public function testInsertUsersRecordsDefaultsMissingSalesforceFieldsToEmptyStrings(): void
    {
        $GLOBALS['wpdb']->var_result = null;
        $GLOBALS['wpdb']->insert_result = 1;

        insert_users_records(['records' => [[]]]);

        self::assertSame([
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'civility' => '',
            'country' => '',
            'postal_code' => '',
            'phone' => '',
        ], $GLOBALS['wpdb']->calls[1]['data']);
    }

    public function testGetPetitionIdFromUidsfReturnsTheFirstMatchingPostId(): void
    {
        $GLOBALS['__phpunit_get_posts_result'] = [123];

        self::assertSame(123, get_petition_id_from_uidsf('ext-999'));

        $args = $GLOBALS['__phpunit_get_posts_calls'][0];
        self::assertSame('petition', $args['post_type']);
        self::assertSame('ext-999', $args['meta_query'][0]['value']);
    }

    public function testGetPetitionIdFromUidsfReturnsNullWhenNoneFound(): void
    {
        $GLOBALS['__phpunit_get_posts_result'] = [];

        self::assertNull(get_petition_id_from_uidsf('unknown'));
    }
}
