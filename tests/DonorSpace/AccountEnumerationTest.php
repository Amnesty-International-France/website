<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/apex-search-fixture.php';

/**
 * The donor space pages must give the same answer whether or not an address
 * has an account or a donor or member record: none of them may reveal it.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AccountEnumerationTest extends TestCase
{
    private const KNOWN = 'known@example.test';
    private const UNKNOWN = 'unknown@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/account-verification-page-stubs.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/authorization.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/user-authentification.php';

        $GLOBALS['__phpunit_users'] = [
            5 => new WP_User(['subscriber'], 5, self::KNOWN, self::KNOWN, '', 'right-password'),
        ];
        $GLOBALS['__phpunit_user_meta'] = [];
        store_email_is_verified(5);
        $GLOBALS['__phpunit_inserted_users'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_signons'] = [];
        $GLOBALS['__phpunit_signon_result'] = new WP_Error('invalid_credentials', 'Invalid credentials');
        $GLOBALS['__phpunit_sent_mails'] = [];
        $GLOBALS['__phpunit_mail_response'] = ['response' => ['code' => 200]];
        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [];
        $GLOBALS['__phpunit_password_checks'] = [];
        $GLOBALS['__phpunit_transients'] = [];
        $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
    }

    /** Queues one Salesforce answer: "donor", "absent" or "ineligible". */
    private function queueSalesforce(string ...$records): void
    {
        $responses = apexSearchFixture();
        foreach ($records as $record) {
            $body = 'contact' === $record
                ? '{"FirstName":"Ada","LastName":"Lovelace"}'
                : json_encode($responses[$record]);
            $GLOBALS['__phpunit_wp_remote_response_queue'][] = ['response' => ['code' => 200], 'body' => $body];
        }
    }

    /**
     * Renders a page and returns its HTML, or the redirect target prefixed
     * with "redirect:".
     */
    private function render(string $page, array $get = [], array $post = []): string
    {
        $_GET = $get;
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = $post === [] ? 'GET' : 'POST';

        ob_start();
        try {
            require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $page;

            return (string) ob_get_contents();
        } catch (AccountVerificationRedirect $redirect) {
            return 'redirect:' . $redirect->getMessage();
        } finally {
            ob_end_clean();
        }
    }

    private function createAccount(string $email): string
    {
        $_POST = ['email' => $email];

        try {
            aif_handle_account_creation_request();
        } catch (AccountVerificationRedirect $redirect) {
            return 'redirect:' . $redirect->getMessage();
        }

        return 'no redirect';
    }

    /** Keeps only the messages shown to the visitor. */
    private function alerts(string $html): string
    {
        preg_match_all('/<div class="partial-alert">(.*?)<\/div>/s', $html, $matches);

        return implode("\n", $matches[1]);
    }

    private function login(string $email, string $password): string
    {
        return $this->render('page-connectez-vous.php', ['user' => ''], ['email' => $email, 'password' => $password]);
    }

    public function testLoginGivesTheSameAnswerForAnUnknownAddressAndAWrongPassword(): void
    {
        $unknown = $this->login(self::UNKNOWN, 'whatever');
        $wrong = $this->login(self::KNOWN, 'whatever');

        self::assertStringContainsString('Mauvais email ou mot de passe', $this->alerts($unknown));
        self::assertSame($this->alerts($unknown), $this->alerts($wrong));
    }

    public function testLoginChecksAPasswordEvenForAnUnknownAddress(): void
    {
        $this->login(self::UNKNOWN, 'whatever');

        self::assertCount(1, $GLOBALS['__phpunit_password_checks']);
    }

    public function testLoginShowsAccountStateOnlyWithTheRightPassword(): void
    {
        delete_user_meta(5, 'email-verfied');

        $wrong = $this->login(self::KNOWN, 'whatever');
        $right = $this->login(self::KNOWN, 'right-password');

        self::assertStringNotContainsString('pas encore vérifié', $wrong);
        self::assertStringContainsString('pas encore vérifié', $right);
    }

    public function testLoginWithAWrongPasswordLeavesTheAccountUntouched(): void
    {
        update_user_meta(5, 'user_SF_ID', 'previous-contact-id');
        $this->queueSalesforce('donor');

        $this->login(self::KNOWN, 'whatever');

        self::assertSame('previous-contact-id', get_user_meta(5, 'user_SF_ID', true));
        self::assertSame([], $GLOBALS['__phpunit_signons']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testLoginPrefillDoesNotLookUpTheAccount(): void
    {
        $html = $this->render('page-connectez-vous.php', ['user' => self::UNKNOWN]);

        self::assertStringContainsString('value="' . self::UNKNOWN . '"', $html);
    }

    public function testLoginIsLimitedPerIpWithTheSameAnswerForEveryAddress(): void
    {
        for ($i = 1; $i <= AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS; $i++) {
            self::assertStringContainsString('Mauvais email ou mot de passe', $this->login(self::UNKNOWN, 'whatever'), "Attempt {$i}");
        }
        $GLOBALS['__phpunit_password_checks'] = [];
        $GLOBALS['__phpunit_signon_result'] = $GLOBALS['__phpunit_users'][5];
        $this->queueSalesforce('donor');

        $known = $this->login(self::KNOWN, 'right-password');
        $unknown = $this->login(self::UNKNOWN, 'whatever');

        self::assertStringContainsString(AIF_LOGIN_RATE_LIMIT_MESSAGE, $this->alerts($known));
        self::assertSame($this->alerts($known), $this->alerts($unknown));
        // Blocked before the account, the password or Salesforce is looked at.
        self::assertSame([], $GLOBALS['__phpunit_password_checks']);
        self::assertSame([], $GLOBALS['__phpunit_signons']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame(0, get_current_user_id());
    }

    public function testLoginLimitOnlyAppliesToTheSameIp(): void
    {
        for ($i = 1; $i <= AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS + 1; $i++) {
            $this->login(self::UNKNOWN, 'whatever');
        }

        $_SERVER['REMOTE_ADDR'] = '198.51.100.20';

        self::assertStringContainsString('Mauvais email ou mot de passe', $this->login(self::UNKNOWN, 'whatever'));
    }

    public function testLoginLimitWindowIsNotExtendedByLaterAttempts(): void
    {
        $this->login(self::UNKNOWN, 'whatever');
        [$key] = array_keys($GLOBALS['__phpunit_transients']);

        // One minute later, another attempt keeps the end of the window.
        $GLOBALS['__phpunit_transients'][$key][0]['start'] -= 60;
        $GLOBALS['__phpunit_transients'][$key][1] -= 60;
        $window_end = $GLOBALS['__phpunit_transients'][$key][0]['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW;
        $this->login(self::UNKNOWN, 'whatever');
        self::assertLessThanOrEqual($window_end, $GLOBALS['__phpunit_transients'][$key][1]);

        // Then the window started 15 minutes ago.

        for ($i = 3; $i <= AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS + 1; $i++) {
            $this->login(self::UNKNOWN, 'whatever');
        }
        self::assertStringContainsString(AIF_LOGIN_RATE_LIMIT_MESSAGE, $this->login(self::UNKNOWN, 'whatever'));

        $GLOBALS['__phpunit_transients'][$key][0]['start'] -= AIF_LOGIN_RATE_LIMIT_WINDOW;

        self::assertStringContainsString('Mauvais email ou mot de passe', $this->login(self::UNKNOWN, 'whatever'));
    }

    public function testPasswordResetGivesTheSameAnswerForAnUnknownAddress(): void
    {
        $post = ['password' => 'Secret#123', 'confirm-password' => 'Secret#123'];
        $known = $this->render('page-modifier-mon-mot-de-passe.php', ['user' => self::KNOWN, 'token' => 'bad'], $post);
        $unknown = $this->render('page-modifier-mon-mot-de-passe.php', ['user' => self::UNKNOWN, 'token' => 'bad'], $post);

        self::assertStringContainsString('Les informations fournies sont erronnées', $known);
        self::assertSame($this->alerts($known), $this->alerts($unknown));
    }

    public function testForgotPasswordGivesTheSameAnswerForAnUnknownAddress(): void
    {
        $known = $this->render('page-mot-de-passe-oublie.php', [], ['email' => self::KNOWN]);
        $unknown = $this->render('page-mot-de-passe-oublie.php', [], ['email' => self::UNKNOWN]);

        self::assertStringContainsString('Si votre adresse est reconnue', $known);
        self::assertSame($this->alerts($known), $this->alerts($unknown));
        self::assertSame([self::KNOWN], array_column($GLOBALS['__phpunit_sent_mails'], 'to'));
    }

    public function testVerificationCodeRequestGivesTheSameAnswerForEveryAddress(): void
    {
        $post = ['form_action' => '2FA_send_code'];
        $GLOBALS['__phpunit_users'][6] = new WP_User(['editor'], 6, 'staff@example.test');

        $this->queueSalesforce('donor', 'donor', 'donor', 'absent', 'ineligible');
        $known = $this->render('page-verifier-votre-email.php', ['user' => self::KNOWN], $post);
        $staff = $this->render('page-verifier-votre-email.php', ['user' => 'staff@example.test'], $post);
        $no_account = $this->render('page-verifier-votre-email.php', ['user' => 'donor-without-account@example.test'], $post);
        $unknown = $this->render('page-verifier-votre-email.php', ['user' => self::UNKNOWN], $post);
        $ineligible = $this->render('page-verifier-votre-email.php', ['user' => 'ineligible@example.test'], $post);

        self::assertStringContainsString('Si votre adresse correspond à un compte', $this->alerts($known));
        foreach ([$staff, $no_account, $unknown, $ineligible] as $other) {
            self::assertSame($this->alerts($known), $this->alerts($other));
        }
        self::assertSame([self::KNOWN], array_column($GLOBALS['__phpunit_sent_mails'], 'to'));
    }

    public function testCodeCheckGivesTheSameAnswerForAnUnknownAddress(): void
    {
        $post = ['form_action' => '2FA_check', '2FA-code' => '000000'];
        $known = $this->render('page-verifier-votre-email.php', ['user' => self::KNOWN], $post);
        $unknown = $this->render('page-verifier-votre-email.php', ['user' => self::UNKNOWN], $post);

        self::assertStringContainsString('Le code fourni est incorrect', $this->alerts($known));
        self::assertSame($this->alerts($known), $this->alerts($unknown));
    }

    public function testCodeCheckGivesTheSameAnswerForABlockedAccountAndAnUnknownAddress(): void
    {
        $post = ['form_action' => '2FA_check', '2FA-code' => '000000'];
        update_user_meta(5, 'login_blocked_until', time() + 3600);

        $blocked = $this->render('page-verifier-votre-email.php', ['user' => self::KNOWN], $post);
        $unknown = $this->render('page-verifier-votre-email.php', ['user' => self::UNKNOWN], $post);

        self::assertSame('blocked', aif_check_2fa_code(5, '000000'));
        self::assertStringContainsString('Le code fourni est incorrect', $this->alerts($blocked));
        self::assertSame($this->alerts($unknown), $this->alerts($blocked));
    }

    public function testAccountCreationGivesTheSameOutcomeForEveryAddress(): void
    {
        $GLOBALS['__phpunit_users'][6] = new WP_User(['editor'], 6, 'staff@example.test');

        $this->queueSalesforce('donor', 'contact');
        $created = $this->render('page-creer-votre-compte.php', [], ['email' => 'new-donor@example.test']);
        // The page declares functions: later requests call its handler directly.
        $this->queueSalesforce('donor', 'contact');
        $existing = $this->createAccount(self::KNOWN);
        $this->queueSalesforce('donor', 'contact');
        $staff = $this->createAccount('staff@example.test');
        $this->queueSalesforce('absent');
        $absent = $this->createAccount(self::UNKNOWN);
        $this->queueSalesforce('ineligible');
        $ineligible = $this->createAccount('ineligible@example.test');

        $verification = 'redirect:https://wordpress.example.test/verifier-votre-email/?user=';
        self::assertSame($verification . rawurlencode('new-donor@example.test'), $created);
        self::assertSame($verification . rawurlencode(self::KNOWN), $existing);
        self::assertSame($verification . rawurlencode('staff@example.test'), $staff);
        self::assertSame($verification . rawurlencode(self::UNKNOWN), $absent);
        self::assertSame($verification . rawurlencode('ineligible@example.test'), $ineligible);
        self::assertSame(['new-donor@example.test'], array_column($GLOBALS['__phpunit_inserted_users'], 'user_email'));
        self::assertSame(['new-donor@example.test'], array_column($GLOBALS['__phpunit_sent_mails'], 'to'));
        self::assertSame('right-password', $GLOBALS['__phpunit_users'][5]->user_pass);
    }
}
