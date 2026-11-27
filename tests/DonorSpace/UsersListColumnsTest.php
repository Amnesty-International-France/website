<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/users-list.php';

if (!class_exists('WP_User_Query')) {
    /**
     * Stand-in for WP_User_Query: query vars and the SQL fragments pre_user_query edits.
     */
    class WP_User_Query
    {
        public string $query_from = 'FROM wp_users';
        public string $query_where = 'WHERE 1=1';

        /**
         * @param array<string,mixed> $query_vars
         */
        public function __construct(public array $query_vars = [])
        {
        }

        public function get(string $query_var): mixed
        {
            return $this->query_vars[$query_var] ?? null;
        }

        public function set(string $query_var, mixed $value): void
        {
            $this->query_vars[$query_var] = $value;
        }
    }
}

/**
 * AIF-443: creation date and verified address in the back-office users list.
 */
final class UsersListColumnsTest extends TestCase
{
    private const USER_ID = 42;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_users'] = [self::USER_ID => new WP_User(['subscriber'], self::USER_ID, 'donor@example.org')];
    }

    public function testCreationDateIsShownInParisTime(): void
    {
        self::assertSame('15/06/2026 14:05', aif_users_list_format_registered('2026-06-15 12:05:00'));
    }

    public function testLateWinterCreationIsShownOnTheNextDay(): void
    {
        self::assertSame('16/01/2026 00:30', aif_users_list_format_registered('2026-01-15 23:30:00'));
    }

    public function testEmptyCreationDateShowsNothing(): void
    {
        self::assertSame('', aif_users_list_format_registered('0000-00-00 00:00:00'));
        self::assertSame('', aif_users_list_format_registered(''));
    }

    public function testUnverifiedSubscriber(): void
    {
        self::assertSame('Non', aif_users_list_verified_label($GLOBALS['__phpunit_users'][self::USER_ID]));
    }

    public function testSubscriberVerifiedBeforeTheDateWasRecorded(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', '1');

        self::assertSame('Oui', aif_users_list_verified_label($GLOBALS['__phpunit_users'][self::USER_ID]));
    }

    public function testSubscriberVerifiedWithADate(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', 'donor@example.org');
        update_user_meta(self::USER_ID, 'email-verified-at', strtotime('2026-11-27 08:15:00 UTC'));

        self::assertSame('Oui, le 27/11/2026 09:15', aif_users_list_verified_label($GLOBALS['__phpunit_users'][self::USER_ID]));
    }

    public function testProofForAnotherAddressIsNotVerified(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', 'old@example.org');

        self::assertSame('Non', aif_users_list_verified_label($GLOBALS['__phpunit_users'][self::USER_ID]));
    }

    public function testUnverifiedQueryKeepsSubscribersWithoutProofForTheirCurrentAddress(): void
    {
        $previous = $GLOBALS['wpdb'];
        $GLOBALS['wpdb'] = (object) ['users' => 'wp_users', 'usermeta' => 'wp_usermeta'];
        $query = new WP_User_Query([AIF_USERS_LIST_UNVERIFIED_ARG => true]);

        try {
            aif_users_list_restrict_unverified($query);
        } finally {
            $GLOBALS['wpdb'] = $previous;
        }

        self::assertSame(
            "FROM wp_users LEFT JOIN wp_usermeta AS aif_ev ON (aif_ev.user_id = wp_users.ID AND aif_ev.meta_key = 'email-verfied')",
            $query->query_from
        );
        // Same rule as get_email_is_verified(): no proof, or neither the legacy "1" nor the current address.
        self::assertSame(
            "WHERE 1=1 AND (aif_ev.meta_value IS NULL OR (aif_ev.meta_value <> '1' AND LOWER(aif_ev.meta_value) <> LOWER(wp_users.user_email)))",
            $query->query_where
        );
    }

    public function testOtherUserQueriesAreLeftUntouched(): void
    {
        $query = new WP_User_Query(['role' => 'subscriber']);

        aif_users_list_restrict_unverified($query);

        self::assertSame('FROM wp_users', $query->query_from);
        self::assertSame('WHERE 1=1', $query->query_where);
    }

    public function testOtherRolesShowNothing(): void
    {
        $editor = new WP_User(['editor'], 7, 'editor@example.org');

        self::assertSame('', aif_users_list_verified_label($editor));
    }
}
