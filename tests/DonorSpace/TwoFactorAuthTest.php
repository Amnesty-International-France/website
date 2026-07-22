<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';

final class TwoFactorAuthTest extends TestCase
{
    private const USER_ID = 42;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_user_meta'] = [];
    }

    public function testStoresAndRetrievesTheTwoFactorCode(): void
    {
        store_2fa_code(self::USER_ID, 123456);

        self::assertSame(123456, get_2fa_code(self::USER_ID));
    }

    public function testStoresAndRetrievesEmailVerifiedFlag(): void
    {
        // get_user_meta() defaults to '' (falsy) when nothing was stored yet -
        // not a strict boolean false.
        self::assertSame('', get_email_is_verified(self::USER_ID));

        store_email_is_verified(self::USER_ID);

        self::assertTrue(get_email_is_verified(self::USER_ID));
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
