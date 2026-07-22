<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/urgent-action/tables.php';

final class UrgentActionTablesTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
    }

    public function testUrgentActionAlreadySignedIsFalseWhenNoMatchingRow(): void
    {
        $GLOBALS['wpdb']->row_result = null;

        self::assertFalse(urgent_action_already_signed(456, 'Email'));

        self::assertSame(
            "SELECT * FROM `wp_aif_urgent_action` WHERE user_id = 456 AND type = 'Email'",
            $GLOBALS['wpdb']->calls[0]['query']
        );
    }

    public function testUrgentActionAlreadySignedIsTrueWhenARowExists(): void
    {
        $GLOBALS['wpdb']->row_result = (object) ['user_id' => 456, 'type' => 'Email'];

        self::assertTrue(urgent_action_already_signed(456, 'Email'));
    }

    public function testInsertUrgentActionSendsExpectedDataAndFormat(): void
    {
        $GLOBALS['wpdb']->insert_result = 1;

        $result = insert_urgent_action('Sms', 456, '2026-07-01 12:00:00', 0, 'droits-des-femmes');

        self::assertTrue($result);
        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('wp_aif_urgent_action', $call['table']);
        self::assertSame([
            'type' => 'Sms',
            'user_id' => 456,
            'created_at' => '2026-07-01 12:00:00',
            'is_sent' => 0,
            'thematique' => 'droits-des-femmes',
        ], $call['data']);
        self::assertSame(['%s', '%d', '%s', '%d', '%s'], $call['format']);
    }

    public function testInsertUrgentActionReturnsFalseOnFailure(): void
    {
        $GLOBALS['wpdb']->insert_result = false;

        self::assertFalse(insert_urgent_action('Email', 456, '2026-07-01 12:00:00', 0));
    }

    public function testGetUnsyncedActionsQueriesActionsNotYetSent(): void
    {
        $GLOBALS['wpdb']->results_result = [['id' => 1]];

        $result = get_unsynced_actions();

        self::assertSame([['id' => 1]], $result);
        $call = $GLOBALS['wpdb']->calls[0];
        self::assertStringContainsString('WHERE is_sent = 0', $call['query']);
        self::assertSame('ARRAY_A', $call['output']);
    }

    public function testUpdateUaSyncsWithSfMarksTheGivenActionAsSent(): void
    {
        update_ua_syncs_with_sf('7');

        $call = $GLOBALS['wpdb']->calls[0];
        self::assertSame('query', $call['method']);
        self::assertSame("UPDATE wp_aif_urgent_action SET is_sent = 1 WHERE id = '7'", $call['query']);
    }

    public function testDeleteUaSynchedDeletesAlreadySentRows(): void
    {
        delete_ua_synched();

        self::assertSame('DELETE FROM wp_aif_urgent_action WHERE is_sent = 1', $GLOBALS['wpdb']->calls[0]['query']);
    }
}
