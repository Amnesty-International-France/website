<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PasswordResetTokenTest extends TestCase
{
    private const USER_ID = 42;
    private const TOKEN = '0123456789abcdef01234567';

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';

        $GLOBALS['__phpunit_user_meta'] = [];
    }

    public function testEmptyTokenIsRejectedWhenNoTokenWasEverStored(): void
    {
        self::assertFalse(is_email_token_valid(self::USER_ID, ''));
    }

    public function testEmptyTokenIsRejectedWhenATokenIsStored(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);

        self::assertFalse(is_email_token_valid(self::USER_ID, ''));
    }

    public function testNonStringTokenIsRejected(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);

        self::assertFalse(is_email_token_valid(self::USER_ID, [self::TOKEN]));
        self::assertFalse(is_email_token_valid(self::USER_ID, null));
    }

    public function testStoredTokenIsAccepted(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);

        self::assertTrue(is_email_token_valid(self::USER_ID, self::TOKEN));
    }

    public function testWrongTokenIsRejected(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);

        self::assertFalse(is_email_token_valid(self::USER_ID, 'not-the-token'));
    }

    public function testTokenIsNotStoredInPlaintext(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);

        self::assertNotContains(self::TOKEN, $GLOBALS['__phpunit_user_meta'][self::USER_ID]);
    }

    public function testExpiredTokenIsRejected(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);
        update_user_meta(self::USER_ID, 'user_email_token_expires', time() - 1);

        self::assertFalse(is_email_token_valid(self::USER_ID, self::TOKEN));
    }

    public function testDeletedTokenCannotBeReused(): void
    {
        store_email_token(self::USER_ID, self::TOKEN);
        delete_email_token(self::USER_ID);

        self::assertFalse(is_email_token_valid(self::USER_ID, self::TOKEN));
    }

    public function testLegacyPlaintextTokenIsNoLongerAccepted(): void
    {
        update_user_meta(self::USER_ID, 'user_email_token', self::TOKEN);

        self::assertFalse(is_email_token_valid(self::USER_ID, self::TOKEN));
    }
}
