<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class EmailVerificationTest extends TestCase
{
    private const USER_ID = 42;

    protected function setUp(): void
    {
        if (!function_exists('wp_get_current_user')) {
            function wp_get_current_user(): WP_User
            {
                return $GLOBALS['__phpunit_current_user'];
            }
        }

        if (!function_exists('wp_clear_auth_cookie')) {
            function wp_clear_auth_cookie(): void
            {
                $GLOBALS['__phpunit_auth_cookie_cleared'] = true;
            }
        }

        if (!function_exists('wp_set_current_user')) {
            function wp_set_current_user(int $user_id): void
            {
                $GLOBALS['__phpunit_current_user_id'] = $user_id;
            }
        }

        if (!function_exists('wp_generate_password')) {
            function wp_generate_password(int $length = 12, bool $special_chars = true, bool $extra_special_chars = false): string
            {
                return 'random-password-' . $length;
            }
        }

        if (!function_exists('wp_set_password')) {
            function wp_set_password(string $password, int $user_id): void
            {
                $GLOBALS['__phpunit_passwords'][$user_id] = $password;
            }
        }

        if (!function_exists('wp_update_user')) {
            function wp_update_user(array $userdata): int
            {
                $user_id = $userdata['ID'];
                $GLOBALS['__phpunit_user_updates'][] = $userdata;
                $GLOBALS['__phpunit_users'][$user_id]->user_activation_key = $userdata['user_activation_key'];

                return $user_id;
            }
        }

        if (!function_exists('get_user_by')) {
            function get_user_by(string $field, string $value): WP_User|false
            {
                foreach ($GLOBALS['__phpunit_users'] as $user) {
                    if (('email' === $field && $user->user_email === $value)
                        || ('login' === $field && $user->user_login === $value)) {
                        return $user;
                    }
                }

                return false;
            }
        }

        if (!function_exists('wp_insert_user')) {
            function wp_insert_user(array $userdata): int|WP_Error
            {
                $GLOBALS['__phpunit_inserted_users'][] = $userdata;

                return $GLOBALS['__phpunit_insert_user_result'];
            }
        }

        if (!function_exists('wp_remote_post')) {
            function wp_remote_post(string $url, array $args = []): array
            {
                $GLOBALS['__phpunit_sent_mails'][] = $args['body'];

                return ['response' => ['code' => 200]];
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/user-authentification.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/index.php';

        $GLOBALS['__phpunit_sent_mails'] = [];
        $GLOBALS['__phpunit_passwords'] = [];
        $GLOBALS['__phpunit_inserted_users'] = [];
        $GLOBALS['__phpunit_insert_user_result'] = 99;
        $GLOBALS['__phpunit_user_updates'] = [];
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_users'] = [self::USER_ID => new WP_User(['subscriber'], self::USER_ID, 'fixture@example.org', 'fixture')];
        $GLOBALS['__phpunit_destroyed_sessions'] = [];
        $GLOBALS['__phpunit_auth_cookie_cleared'] = false;
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_current_user'] = new WP_User();
    }

    public function testSubscriberRoleIsDetected(): void
    {
        self::assertTrue(aif_is_subscriber(new WP_User(['subscriber'], self::USER_ID)));
        self::assertFalse(aif_is_subscriber(new WP_User(['editor'], self::USER_ID)));
        self::assertFalse(aif_is_subscriber(new WP_User([], self::USER_ID)));
    }

    public function testUnverifiedSubscriberIsDetected(): void
    {
        self::assertTrue(aif_is_unverified_subscriber(new WP_User(['subscriber'], self::USER_ID)));
    }

    public function testVerifiedSubscriberIsNotDetected(): void
    {
        store_email_is_verified(self::USER_ID);

        self::assertFalse(aif_is_unverified_subscriber(new WP_User(['subscriber'], self::USER_ID)));
    }

    public function testLegacyStringOneRequiresReverification(): void
    {
        update_user_meta(self::USER_ID, 'email-verfied', '1');

        self::assertTrue(aif_is_unverified_subscriber(new WP_User(['subscriber'], self::USER_ID)));
    }

    public function testAdministratorWithoutMetaIsNotBlocked(): void
    {
        $admin = new WP_User(['administrator'], self::USER_ID);

        self::assertFalse(aif_is_unverified_subscriber($admin));
        self::assertSame($admin, aif_reject_unverified_subscriber_login($admin));
    }

    public function testLoginOfUnverifiedSubscriberIsRejected(): void
    {
        $result = aif_reject_unverified_subscriber_login(new WP_User(['subscriber'], self::USER_ID));

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('aif_email_not_verified', $result->get_error_code());
    }

    public function testLoginOfVerifiedSubscriberPassesThrough(): void
    {
        store_email_is_verified(self::USER_ID);
        $user = new WP_User(['subscriber'], self::USER_ID);

        self::assertSame($user, aif_reject_unverified_subscriber_login($user));
    }

    public function testEarlierAuthenticationResultsPassThrough(): void
    {
        $error = new WP_Error('incorrect_password', 'Wrong password');

        self::assertSame($error, aif_reject_unverified_subscriber_login($error));
        self::assertNull(aif_reject_unverified_subscriber_login(null));
    }

    public function testHooksAreRegistered(): void
    {
        self::assertContains('aif_reject_unverified_subscriber_login', $GLOBALS['__phpunit_registered_filters']['authenticate'] ?? []);
        self::assertContains('aif_log_out_unverified_subscriber', $GLOBALS['__phpunit_registered_actions']['init'] ?? []);
        self::assertContains('aif_reset_email_verification_on_email_change', $GLOBALS['__phpunit_registered_actions']['profile_update'] ?? []);
        self::assertContains('aif_record_native_password_reset_email', $GLOBALS['__phpunit_registered_actions']['retrieve_password_key'] ?? []);
        self::assertContains('aif_verify_email_after_native_password_reset', $GLOBALS['__phpunit_registered_actions']['after_password_reset'] ?? []);
    }

    public function testApplicationPasswordsAreUnavailableToSubscribers(): void
    {
        self::assertContains('aif_disable_application_passwords_for_subscribers', $GLOBALS['__phpunit_registered_filters']['wp_is_application_passwords_available_for_user'] ?? []);

        // Verified too: a password created before verification would survive it.
        store_email_is_verified(self::USER_ID);

        self::assertFalse(aif_disable_application_passwords_for_subscribers(true, new WP_User(['subscriber'], self::USER_ID)));
    }

    public function testApplicationPasswordsStayAvailableToStaff(): void
    {
        self::assertTrue(aif_disable_application_passwords_for_subscribers(true, new WP_User(['editor'], self::USER_ID)));
        self::assertFalse(aif_disable_application_passwords_for_subscribers(false, new WP_User(['editor'], self::USER_ID)));
    }

    public function testInitHookLogsOutAnUnverifiedSubscriber(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = self::USER_ID;
        $GLOBALS['__phpunit_current_user'] = new WP_User(['subscriber'], self::USER_ID);

        aif_log_out_unverified_subscriber();

        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
        self::assertTrue($GLOBALS['__phpunit_auth_cookie_cleared']);
        self::assertSame(0, $GLOBALS['__phpunit_current_user_id']);
    }

    public function testInitHookLeavesVerifiedSubscribersLoggedIn(): void
    {
        store_email_is_verified(self::USER_ID);
        $GLOBALS['__phpunit_current_user_id'] = self::USER_ID;
        $GLOBALS['__phpunit_current_user'] = new WP_User(['subscriber'], self::USER_ID);

        aif_log_out_unverified_subscriber();

        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
        self::assertSame(self::USER_ID, $GLOBALS['__phpunit_current_user_id']);
    }

    public function testInitHookLeavesAdministratorsLoggedIn(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = self::USER_ID;
        $GLOBALS['__phpunit_current_user'] = new WP_User(['administrator'], self::USER_ID);

        aif_log_out_unverified_subscriber();

        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testInitHookIgnoresVisitors(): void
    {
        aif_log_out_unverified_subscriber();

        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
        self::assertFalse($GLOBALS['__phpunit_auth_cookie_cleared']);
    }

    public function testEmailChangeResetsVerification(): void
    {
        store_email_is_verified(self::USER_ID);
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'victim@example.org');

        aif_reset_email_verification_on_email_change(
            self::USER_ID,
            new WP_User(['subscriber'], self::USER_ID, 'attacker@example.org')
        );

        self::assertFalse(get_email_is_verified(self::USER_ID));
        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testEmailChangeInvalidatesCodeSentToOldAddress(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'attacker@example.org');
        store_2fa_code(self::USER_ID, '123456');
        self::assertTrue(is_2fa_code_valid(self::USER_ID, '123456'));

        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'victim@example.org';
        aif_reset_email_verification_on_email_change(self::USER_ID, new WP_User(['subscriber'], self::USER_ID, 'attacker@example.org'));

        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertSame('', get_user_meta(self::USER_ID, '2fa_code_hash', true));
    }

    public function testEmailChangeInvalidatesResetTokenSentToOldAddress(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'attacker@example.org');
        store_email_token(self::USER_ID, 'old-reset-token');
        self::assertTrue(is_email_token_valid(self::USER_ID, 'old-reset-token'));

        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'victim@example.org';
        aif_reset_email_verification_on_email_change(self::USER_ID, new WP_User(['subscriber'], self::USER_ID, 'attacker@example.org'));

        self::assertFalse(is_email_token_valid(self::USER_ID, 'old-reset-token'));
        self::assertSame('', get_user_meta(self::USER_ID, 'user_email_token_hash', true));
    }

    public function testProofIsBoundToAddressEvenBeforeCleanupRuns(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        store_2fa_code(self::USER_ID, '123456');
        store_email_token(self::USER_ID, 'old-reset-token');

        $user->user_email = 'victim@example.org';

        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertFalse(is_email_token_valid(self::USER_ID, 'old-reset-token'));
    }

    public function testFreshProofsWorkAfterEmailChange(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        $user->user_email = 'victim@example.org';
        aif_reset_email_verification_on_email_change(self::USER_ID, new WP_User(['subscriber'], self::USER_ID, 'fixture@example.org'));

        store_2fa_code(self::USER_ID, '654321');
        store_email_token(self::USER_ID, 'new-reset-token');

        self::assertTrue(is_2fa_code_valid(self::USER_ID, '654321'));
        self::assertTrue(is_email_token_valid(self::USER_ID, 'new-reset-token'));
    }

    public function testEmailChangeInvalidatesNativeResetKey(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        $user->user_activation_key = 'hashed-core-key';
        aif_record_native_password_reset_email('fixture');

        $user->user_email = 'victim@example.org';
        aif_reset_email_verification_on_email_change(self::USER_ID, new WP_User(['subscriber'], self::USER_ID, 'fixture@example.org'));

        self::assertSame('', $user->user_activation_key);
        self::assertSame('', get_user_meta(self::USER_ID, 'aif_native_password_reset_email', true));
        self::assertSame([['ID' => self::USER_ID, 'user_activation_key' => '']], $GLOBALS['__phpunit_user_updates']);
    }

    public function testCaseOnlyEmailChangeKeepsVerification(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'Jane@Example.org';
        store_email_is_verified(self::USER_ID);
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'jane@example.org');

        aif_reset_email_verification_on_email_change(
            self::USER_ID,
            new WP_User(['subscriber'], self::USER_ID, 'Jane@Example.org')
        );

        self::assertTrue(get_email_is_verified(self::USER_ID));
    }

    public function testProfileUpdateWithoutEmailChangeKeepsVerification(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID]->user_email = 'jane@example.org';
        store_email_is_verified(self::USER_ID);
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'jane@example.org');

        aif_reset_email_verification_on_email_change(
            self::USER_ID,
            new WP_User(['subscriber'], self::USER_ID, 'jane@example.org')
        );

        self::assertTrue(get_email_is_verified(self::USER_ID));
    }

    public function testDestroyUserSessions(): void
    {
        aif_destroy_user_sessions(self::USER_ID);

        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testPrepareAccountCreatesASubscriberWithARandomPassword(): void
    {
        $user_id = aif_prepare_account_for_verification('donor@example.org', 'Ada', 'Lovelace');

        self::assertSame(99, $user_id);
        self::assertCount(1, $GLOBALS['__phpunit_inserted_users']);
        $userdata = $GLOBALS['__phpunit_inserted_users'][0];
        self::assertSame('donor@example.org', $userdata['user_login']);
        self::assertSame('donor@example.org', $userdata['user_email']);
        self::assertSame('random-password-32', $userdata['user_pass']);
        self::assertSame('subscriber', $userdata['role']);
        self::assertSame('Ada Lovelace', $userdata['nickname']);
    }

    public function testPrepareAccountReusesUnverifiedAccount(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'donor@example.org');

        self::assertSame(self::USER_ID, aif_prepare_account_for_verification('donor@example.org', 'Ada', 'Lovelace'));
        self::assertSame([], $GLOBALS['__phpunit_inserted_users']);
    }

    public function testPrepareAccountRefusesVerifiedAccount(): void
    {
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'donor@example.org');
        store_email_is_verified(self::USER_ID);

        $result = aif_prepare_account_for_verification('donor@example.org', 'Ada', 'Lovelace');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('aif_account_exists', $result->get_error_code());
    }

    public function testPrepareAccountRefusesStaffAccount(): void
    {
        // Verification replaces the password: it must never reach a staff account.
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['editor'], self::USER_ID, 'donor@example.org');

        $result = aif_prepare_account_for_verification('donor@example.org', 'Ada', 'Lovelace');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('aif_account_exists', $result->get_error_code());
    }

    public function testPrepareAccountReturnsInsertErrors(): void
    {
        $GLOBALS['__phpunit_insert_user_result'] = new WP_Error('existing_user_login', 'Taken');

        $result = aif_prepare_account_for_verification('donor@example.org', 'Ada', 'Lovelace');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('existing_user_login', $result->get_error_code());
    }

    public function testCodeIsSentToTheStoredAddressOnly(): void
    {
        // The account lookup is accent- and case-insensitive in MySQL, so the
        // address a visitor typed may differ from the one the account holds.
        $GLOBALS['__phpunit_users'][self::USER_ID] = new WP_User(['subscriber'], self::USER_ID, 'victim@orange.fr');

        self::assertTrue(aif_send_verification_code(self::USER_ID));

        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        $mail = $GLOBALS['__phpunit_sent_mails'][0];
        self::assertSame('victim@orange.fr', $mail['to']);
        $code = json_decode($mail['t:variables'], true)['code'];
        self::assertTrue(is_2fa_code_valid(self::USER_ID, $code));
    }

    public function testNoCodeIsSentForAnUnknownUser(): void
    {
        unset($GLOBALS['__phpunit_users'][self::USER_ID]);

        self::assertFalse(aif_send_verification_code(self::USER_ID));
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testPasswordResetVerifiesASubscriber(): void
    {
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, 'login_attempts', 3);

        aif_verify_email_on_password_reset(new WP_User(['subscriber'], self::USER_ID));

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertSame(0, get_user_meta(self::USER_ID, 'login_attempts', true));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
    }

    public function testPasswordResetLeavesStaffUnflagged(): void
    {
        aif_verify_email_on_password_reset(new WP_User(['editor'], self::USER_ID));

        self::assertFalse(get_email_is_verified(self::USER_ID));
    }

    public function testNativePasswordResetVerifiesTheAddressToWhichItsLinkWasIssued(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, 'login_attempts', 3);
        aif_record_native_password_reset_email('fixture');

        aif_verify_email_after_native_password_reset($user);

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertSame(0, get_user_meta(self::USER_ID, 'login_attempts', true));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
        self::assertSame('', get_user_meta(self::USER_ID, 'aif_native_password_reset_email', true));
    }

    public function testNativePasswordResetWithoutRecordedAddressDoesNotVerify(): void
    {
        aif_verify_email_after_native_password_reset($GLOBALS['__phpunit_users'][self::USER_ID]);

        self::assertFalse(get_email_is_verified(self::USER_ID));
        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testNativePasswordResetCannotVerifyChangedAddress(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        aif_record_native_password_reset_email('fixture');
        $user->user_email = 'victim@example.org';

        aif_verify_email_after_native_password_reset($user);

        self::assertFalse(get_email_is_verified(self::USER_ID));
        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testNewNativeResetLinkCanVerifyChangedAddress(): void
    {
        $user = $GLOBALS['__phpunit_users'][self::USER_ID];
        $user->user_email = 'victim@example.org';
        aif_reset_email_verification_on_email_change(self::USER_ID, new WP_User(['subscriber'], self::USER_ID, 'fixture@example.org'));
        aif_record_native_password_reset_email('fixture');

        aif_verify_email_after_native_password_reset($user);

        self::assertTrue(get_email_is_verified(self::USER_ID));
    }

    public function testNativePasswordResetLeavesStaffUnflagged(): void
    {
        $user = new WP_User(['editor'], self::USER_ID, 'staff@example.org', 'staff');
        $GLOBALS['__phpunit_users'][self::USER_ID] = $user;
        aif_record_native_password_reset_email('staff');

        aif_verify_email_after_native_password_reset($user);

        self::assertFalse(get_email_is_verified(self::USER_ID));
        self::assertSame([], $GLOBALS['__phpunit_destroyed_sessions']);
    }

    public function testCompletingVerificationResetsPasswordSessionsAndCode(): void
    {
        store_2fa_code(self::USER_ID, '123456');
        update_user_meta(self::USER_ID, 'login_attempts', 3);

        $token = aif_complete_email_verification(self::USER_ID);

        self::assertTrue(get_email_is_verified(self::USER_ID));
        self::assertFalse(is_2fa_code_valid(self::USER_ID, '123456'));
        self::assertSame(0, get_user_meta(self::USER_ID, 'login_attempts', true));
        self::assertSame('random-password-32', $GLOBALS['__phpunit_passwords'][self::USER_ID]);
        self::assertSame([self::USER_ID], $GLOBALS['__phpunit_destroyed_sessions']);
        self::assertTrue(is_email_token_valid(self::USER_ID, $token));
    }
}
