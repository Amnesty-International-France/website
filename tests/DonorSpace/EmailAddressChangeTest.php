<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class EmailAddressChangeTest extends TestCase
{
    private const USER_ID = 42;

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/index.php';

        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_users'] = [self::USER_ID => new WP_User(['subscriber'], self::USER_ID, 'old@example.test')];
        $GLOBALS['__phpunit_destroyed_sessions'] = [];
    }

    public static function verifiedFlags(): iterable
    {
        yield 'address-bound proof' => ['old@example.test'];
        yield 'historical verification' => ['1'];
    }

    #[DataProvider('verifiedFlags')]
    public function testEmailChangeRevokesVerificationCodeResetTokenAndSession(string $flag): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', $flag);
        self::assertTrue(get_email_is_verified(self::USER_ID));
        store_2fa_code(self::USER_ID, '123456');
        store_email_token(self::USER_ID, 'reset-secret');

        $old_user = clone $GLOBALS['__phpunit_users'][self::USER_ID];
        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'new@example.test';
        aif_reset_email_verification_on_email_change(self::USER_ID, $old_user);

        self::assertFalse(get_email_is_verified(self::USER_ID));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertFalse(is_email_token_valid(self::USER_ID, 'reset-secret'));
        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    #[DataProvider('verifiedFlags')]
    public function testCaseOnlyEmailChangeKeepsCurrentVerification(string $flag): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', $flag);
        $old_user = clone $GLOBALS['__phpunit_users'][self::USER_ID];
        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'OLD@example.test';

        aif_reset_email_verification_on_email_change(self::USER_ID, $old_user);

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
    }
}
