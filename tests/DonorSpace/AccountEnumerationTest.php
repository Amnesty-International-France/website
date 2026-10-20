<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The donor space pages and the newsletter page must give the same answer
 * whether or not an address has an account, a donor or member record, or a
 * signature: none of them may reveal it.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AccountEnumerationTest extends TestCase
{
    private const KNOWN = 'known@example.test';
    private const UNKNOWN = 'unknown@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/account-enumeration-stubs.php';

        $GLOBALS['__phpunit_wp_users'] = [
            self::KNOWN => (object) ['ID' => 5, 'user_email' => self::KNOWN, 'user_pass' => 'right-password'],
        ];
        $GLOBALS['__phpunit_verified_users'] = [5 => true];
        $GLOBALS['__phpunit_sf_members'] = [
            self::KNOWN => (object) ['Id' => 'contact-id', 'isDonateur' => true, 'isMembre' => false],
        ];
        $GLOBALS['__phpunit_user_lookups'] = [];
        $GLOBALS['__phpunit_signer_lookups'] = [];
        $GLOBALS['__phpunit_sent_emails'] = [];
        $GLOBALS['__phpunit_inserted_users'] = [];
        $GLOBALS['__phpunit_get_posts_result'] = [];

        $dir = sys_get_temp_dir() . '/aif-account-enumeration-test';
        @mkdir($dir . '/assets/images', 0o777, true);
        file_put_contents($dir . '/assets/images/icon-letters.svg', '<svg></svg>');
        $GLOBALS['__phpunit_template_directory'] = $dir;
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
        } catch (RedirectTestException $redirect) {
            ob_end_clean();

            return 'redirect:' . $redirect->getMessage();
        }

        return (string) ob_get_clean();
    }

    private function createAccount(array $post): string
    {
        $_POST = $post;

        try {
            aif_handle_account_creation_request();
        } catch (RedirectTestException $redirect) {
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

    public function testLoginGivesTheSameAnswerForAnUnknownAddressAndAWrongPassword(): void
    {
        $unknown = $this->render('page-connectez-vous.php', ['user' => ''], ['email' => self::UNKNOWN, 'password' => 'whatever']);
        $wrong = $this->render('page-connectez-vous.php', ['user' => ''], ['email' => self::KNOWN, 'password' => 'whatever']);

        self::assertStringContainsString('Mauvais email ou mot de passe', $this->alerts($unknown));
        self::assertSame($this->alerts($unknown), $this->alerts($wrong));
    }

    public function testLoginChecksAPasswordEvenForAnUnknownAddress(): void
    {
        $GLOBALS['__phpunit_password_checks'] = [];

        $this->render('page-connectez-vous.php', ['user' => ''], ['email' => self::UNKNOWN, 'password' => 'whatever']);

        self::assertCount(1, $GLOBALS['__phpunit_password_checks']);
    }

    public function testPasswordResetGivesTheSameAnswerForAnUnknownAddress(): void
    {
        $post = ['password' => 'Secret#123', 'confirm-password' => 'Secret#123'];
        $known = $this->render('page-modifier-mon-mot-de-passe.php', ['user' => self::KNOWN, 'token' => 'bad'], $post);
        $unknown = $this->render('page-modifier-mon-mot-de-passe.php', ['user' => self::UNKNOWN, 'token' => 'bad'], $post);

        self::assertStringContainsString('Les informations fournies sont erronnées', $known);
        self::assertSame($this->alerts($known), $this->alerts($unknown));
    }

    public function testLoginShowsAccountStateOnlyWithTheRightPassword(): void
    {
        $GLOBALS['__phpunit_verified_users'] = [];

        $wrong = $this->render('page-connectez-vous.php', ['user' => ''], ['email' => self::KNOWN, 'password' => 'whatever']);
        $right = $this->render('page-connectez-vous.php', ['user' => ''], ['email' => self::KNOWN, 'password' => 'right-password']);

        self::assertStringNotContainsString('pas encore vérifié', $wrong);
        self::assertStringContainsString('pas encore vérifié', $right);
    }

    public function testLoginPrefillDoesNotLookUpTheAccount(): void
    {
        $html = $this->render('page-connectez-vous.php', ['user' => self::UNKNOWN]);

        self::assertStringContainsString('value="' . self::UNKNOWN . '"', $html);
        self::assertSame([], $GLOBALS['__phpunit_user_lookups']);
    }

    public function testForgotPasswordGivesTheSameAnswerForAnUnknownAddress(): void
    {
        $known = $this->render('page-mot-de-passe-oublie.php', [], ['email' => self::KNOWN]);
        $unknown = $this->render('page-mot-de-passe-oublie.php', [], ['email' => self::UNKNOWN]);

        self::assertStringContainsString('Si votre adresse est reconnue', $known);
        self::assertSame($this->alerts($known), $this->alerts($unknown));
        self::assertSame([self::KNOWN], $GLOBALS['__phpunit_sent_emails']);
    }

    public function testVerificationCodeRequestGivesTheSameAnswerForEveryAddress(): void
    {
        $post = ['form_action' => '2FA_send_code'];
        $known = $this->render('page-verifier-votre-email.php', ['user' => self::KNOWN], $post);

        // A donor without an account, and an address unknown to Salesforce.
        $GLOBALS['__phpunit_sf_members']['donor-without-account@example.test'] = (object) ['Id' => 'c2', 'isDonateur' => true];
        $no_account = $this->render('page-verifier-votre-email.php', ['user' => 'donor-without-account@example.test'], $post);
        $unknown = $this->render('page-verifier-votre-email.php', ['user' => self::UNKNOWN], $post);

        self::assertStringContainsString('Si votre adresse correspond à un compte', $known);
        self::assertSame($this->alerts($known), $this->alerts($no_account));
        self::assertSame($this->alerts($known), $this->alerts($unknown));
        self::assertSame([self::KNOWN], $GLOBALS['__phpunit_sent_emails']);
    }

    public function testAccountCreationGivesTheSameOutcomeForEveryAddress(): void
    {
        $post = ['email' => '', 'password' => 'Secret#123', 'confirm-password' => 'Secret#123'];

        $GLOBALS['__phpunit_sf_members']['new-donor@example.test'] = (object) ['Id' => 'c3', 'isDonateur' => true];
        $created = $this->render('page-creer-votre-compte.php', [], ['email' => 'new-donor@example.test'] + $post);
        // The page declares functions: later requests call its handler directly.
        $existing = $this->createAccount(['email' => self::KNOWN] + $post);
        $no_access = $this->createAccount(['email' => self::UNKNOWN] + $post);

        $verification = 'redirect:https://wordpress.example.test/verifier-votre-email/?user=';
        self::assertSame($verification . urlencode('new-donor@example.test'), $created);
        self::assertSame($verification . urlencode(self::KNOWN), $existing);
        self::assertSame($verification . urlencode(self::UNKNOWN), $no_access);
        self::assertSame(['new-donor@example.test'], $GLOBALS['__phpunit_inserted_users']);
        self::assertSame(['new-donor@example.test'], $GLOBALS['__phpunit_sent_emails']);
    }

    public function testNewsletterPageDoesNotLookUpTheAddressFromTheUrl(): void
    {
        $html = $this->render('patterns/page-nl-content.php', ['email' => self::KNOWN]);

        self::assertSame([], $GLOBALS['__phpunit_signer_lookups']);
        self::assertStringContainsString('id="lastname"', $html);
        self::assertStringContainsString('value="' . self::KNOWN . '"', $html);
    }
}
