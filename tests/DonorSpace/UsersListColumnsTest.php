<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/users-list.php';

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

    public function testOtherRolesShowNothing(): void
    {
        $editor = new WP_User(['editor'], 7, 'editor@example.org');

        self::assertSame('', aif_users_list_verified_label($editor));
    }
}
