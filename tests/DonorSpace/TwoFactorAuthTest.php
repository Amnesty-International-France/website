<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';

final class TwoFactorAuthTest extends TestCase
{
    private const USER_ID = 42;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_users'] = [self::USER_ID => new WP_User(['subscriber'], self::USER_ID, 'donor@example.org')];
    }

    public function testGeneratedCodeIsASixDigitString(): void
    {
        $code = generate_2fa_code();

        self::assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    public function testStoresOnlyAHashOfTheCode(): void
    {
        update_user_meta(self::USER_ID, '2fa_code', 111111);

        store_2fa_code(self::USER_ID, '123456');

        self::assertSame(hash('sha256', "donor@example.org\0" . '123456'), get_user_meta(self::USER_ID, '2fa_code_hash', true));
        self::assertSame('', get_user_meta(self::USER_ID, '2fa_code', true));
    }

    public function testStoredCodeIsValidBeforeExpiry(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        self::assertTrue(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, '654321'));
    }

    public function testExpiredCodeIsInvalid(): void
    {
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, '2fa_code_expires', time() - 1);

        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
    }

    public function testLegacyPlaintextCodeIsNeverAccepted(): void
    {
        update_user_meta(self::USER_ID, '2fa_code', 123456);

        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
    }

    public function testEmptyOrNonStringCodeIsInvalid(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        self::assertFalse(is_2fa_code_valid(self::USER_ID, ''));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, 123456));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, null));
    }

    public function testDeleteCodeInvalidatesIt(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        delete_2fa_code(self::USER_ID);

        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
    }

    public function testCheckReturnsValidForTheCorrectCode(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        self::assertSame('valid', aif_check_2fa_code(self::USER_ID, '123456'));
    }

    public function testCheckCountsAWrongCodeAsAnAttempt(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        self::assertSame('invalid', aif_check_2fa_code(self::USER_ID, '000000'));
        self::assertSame(1, get_user_meta(self::USER_ID, 'login_attempts', true));
    }

    public function testCheckBlocksAfterTenWrongCodes(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        for ($i = 1; $i <= 10; $i++) {
            self::assertSame('invalid', aif_check_2fa_code(self::USER_ID, '000000'), "Attempt {$i}");
        }

        self::assertSame('blocked', aif_check_2fa_code(self::USER_ID, '000000'));
    }

    public function testCorrectCodeIsRefusedAfterTenWrongCodes(): void
    {
        store_2fa_code(self::USER_ID, '123456');

        for ($i = 1; $i <= 10; $i++) {
            aif_check_2fa_code(self::USER_ID, '000000');
        }

        self::assertSame('blocked', aif_check_2fa_code(self::USER_ID, '123456'));
    }

    public function testCorrectCodeIsRefusedWhileBlocked(): void
    {
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, 'login_blocked_until', time() + 3600);

        self::assertSame('blocked', aif_check_2fa_code(self::USER_ID, '123456'));
    }

    public function testCorrectCodeIsAcceptedOnceTheBlockHasExpired(): void
    {
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, 'login_attempts', 10);
        update_user_meta(self::USER_ID, 'login_blocked_until', time() - 1);

        self::assertSame('valid', aif_check_2fa_code(self::USER_ID, '123456'));
    }

    public function testIsLoginBlockedFollowsTheBlockWindow(): void
    {
        self::assertFalse(is_login_blocked(self::USER_ID));

        update_user_meta(self::USER_ID, 'login_blocked_until', time() + 3600);
        self::assertTrue(is_login_blocked(self::USER_ID));

        update_user_meta(self::USER_ID, 'login_blocked_until', time() - 1);
        self::assertFalse(is_login_blocked(self::USER_ID));
    }

    public function testStoresAndRetrievesEmailVerifiedFlag(): void
    {
        self::assertFalse(get_email_is_verified(self::USER_ID));

        store_email_is_verified(self::USER_ID);

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertSame('donor@example.org', get_user_meta(self::USER_ID, 'email-verfied', true));
    }

    public function testVerifiedAddressDoesNotAuthorizeAnotherAddress(): void
    {
        store_email_is_verified(self::USER_ID);

        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'other@example.org';

        self::assertFalse(get_email_is_verified(self::USER_ID));
    }

    public function testLegacyVerifiedFlagIsAcceptedWithoutRewritingIt(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', '1');

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertSame('1', get_user_meta(self::USER_ID, 'email-verfied', true));
    }

    #[DataProvider('unverifiedFlags')]
    public function testUnverifiedOrUnrecognizedFlagsAreNotAccepted(mixed $flag): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', $flag);

        self::assertFalse(get_email_is_verified(self::USER_ID));
    }

    public static function unverifiedFlags(): iterable
    {
        yield 'empty' => [''];
        yield 'false' => [false];
        yield 'zero' => [0];
        yield 'persisted zero' => ['0'];
        yield 'unrecognized' => ['yes'];
        yield 'another address' => ['other@example.org'];
    }

    public function testLegacyFlagDoesNotVerifyAMissingUser(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', '1');
        unset($GLOBALS['__phpunit_users'][self::USER_ID]);

        self::assertFalse(get_email_is_verified(self::USER_ID));
    }

    public function testCanCheckCodeIsFalseWhenNeverBlocked(): void
    {
        self::assertFalse(can_check_code(self::USER_ID));
    }

    public function testCanCheckCodeIsFalseWhileBlockWindowIsStillInTheFuture(): void
    {
        update_user_meta(self::USER_ID, 'login_blocked_until', time() + 3600);

        self::assertFalse(can_check_code(self::USER_ID));
    }

    public function testCanCheckCodeIsTrueOnceTheBlockWindowHasPassed(): void
    {
        update_user_meta(self::USER_ID, 'login_blocked_until', time() - 1);

        self::assertTrue(can_check_code(self::USER_ID));
    }

    public function testFirstTenLoginAttemptsAreAllowedAndIncrementTheCounter(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            self::assertTrue(limit_login_attempts(self::USER_ID), "Attempt {$i} should be allowed");
        }

        self::assertSame(10, get_user_meta(self::USER_ID, 'login_attempts', true));
    }

    public function testEleventhLoginAttemptIsBlockedAndSetsABlockWindow(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            limit_login_attempts(self::USER_ID);
        }

        self::assertFalse(limit_login_attempts(self::USER_ID));
        self::assertGreaterThan(time(), get_login_blocked_until(self::USER_ID));
        // The blocking call does not increment the counter further.
        self::assertSame(10, get_user_meta(self::USER_ID, 'login_attempts', true));
    }

    public function testAttemptsAutoResetOnceTheBlockWindowHasPassed(): void
    {
        update_user_meta(self::USER_ID, 'login_attempts', 15);
        update_user_meta(self::USER_ID, 'login_blocked_until', time() - 1);

        // can_check_code() is true (block window elapsed), so the attempt
        // counter is reset before this call is evaluated as attempt #1.
        self::assertTrue(limit_login_attempts(self::USER_ID));
        self::assertSame(1, get_user_meta(self::USER_ID, 'login_attempts', true));
        self::assertSame('', get_login_blocked_until(self::USER_ID));
    }

    public function testResetLoginAttemptsClearsCounterAndBlockWindow(): void
    {
        update_user_meta(self::USER_ID, 'login_attempts', 7);
        update_user_meta(self::USER_ID, 'login_blocked_until', time() + 3600);

        reset_login_attempts(self::USER_ID);

        self::assertSame(0, get_user_meta(self::USER_ID, 'login_attempts', true));
        self::assertSame('', get_login_blocked_until(self::USER_ID));
    }
}
