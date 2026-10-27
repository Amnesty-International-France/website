<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/apex-search-fixture.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AccountVerificationPagesTest extends TestCase
{
    private const EMAIL = 'donor+tag@example.test';

    protected function setUp(): void
    {
        require_once __DIR__ . '/account-verification-page-stubs.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/authorization.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/index.php';

        $GLOBALS['__phpunit_users'] = [];
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_inserted_users'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_signons'] = [];
        $GLOBALS['__phpunit_signon_result'] = new WP_Error('invalid_credentials', 'Invalid credentials');
        $GLOBALS['__phpunit_sent_mails'] = [];
        $GLOBALS['__phpunit_mail_response'] = ['response' => ['code' => 200]];
        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $this->queueEligibleDonor();
    }

    private function queueEligibleDonor(): void
    {
        $responses = apexSearchFixture();
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [
            ['response' => ['code' => 200], 'body' => json_encode($responses['donor'])],
            ['response' => ['code' => 200], 'body' => '{"FirstName":"Ada","LastName":"Lovelace"}'],
        ];
    }

    private function render(string $page, array $get, array $post): string
    {
        $_GET = $get;
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = 'POST';

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

    #[DataProvider('accountStateProvider')]
    public function testFailedDeliveryKeepsSignupRecoverableWithoutCreatingADuplicate(bool $existing): void
    {
        if ($existing) {
            $GLOBALS['__phpunit_users'][42] = new WP_User(['subscriber'], 42, self::EMAIL);
        }
        $GLOBALS['__phpunit_mail_response'] = new WP_Error('http_request_failed', 'Mail service unavailable');

        $html = $this->render('page-creer-votre-compte.php', [], ['email' => self::EMAIL]);

        self::assertStringContainsString("L'envoi du code de vérification a échoué", $html);
        self::assertStringContainsString('Créer mon compte', $html);
        self::assertFalse(get_email_is_verified(42));
        self::assertCount($existing ? 0 : 1, $GLOBALS['__phpunit_inserted_users']);

        // A second POST can reuse the unverified account and send a fresh code.
        $GLOBALS['__phpunit_mail_response'] = ['response' => ['code' => 200]];
        $this->queueEligibleDonor();
        try {
            aif_handle_account_creation_request();
            self::fail('Successful delivery should redirect to verification.');
        } catch (AccountVerificationRedirect $redirect) {
            self::assertSame('https://wordpress.example.test/verifier-votre-email/?user=donor%2Btag%40example.test', $redirect->getMessage());
        }

        self::assertCount($existing ? 0 : 1, $GLOBALS['__phpunit_inserted_users']);
        self::assertCount(2, $GLOBALS['__phpunit_sent_mails']);
        $mail = $GLOBALS['__phpunit_sent_mails'][1];
        self::assertSame(self::EMAIL, $mail['to']);
        self::assertTrue(is_2fa_code_valid(42, json_decode($mail['t:variables'], true)['code']));
    }

    public static function accountStateProvider(): iterable
    {
        yield 'new account' => [false];
        yield 'existing unverified subscriber' => [true];
    }

    public function testSuccessfulSignupStillRedirectsToVerification(): void
    {
        $result = $this->render('page-creer-votre-compte.php', [], ['email' => self::EMAIL]);

        self::assertSame('redirect:https://wordpress.example.test/verifier-votre-email/?user=donor%2Btag%40example.test', $result);
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        self::assertFalse(get_email_is_verified(42));
    }

    public function testResendForAnEligibleNonSubscriberPointsToLoginWithoutSendingACode(): void
    {
        $GLOBALS['__phpunit_users'][42] = new WP_User(['editor'], 42, self::EMAIL, 'staff', 'native-reset-key', 'existing-password');
        update_user_meta(42, 'email-verfied', '1');

        $html = $this->render('page-verifier-votre-email.php', ['user' => self::EMAIL], ['form_action' => '2FA_send_code']);

        self::assertStringContainsString("href='https://wordpress.example.test/connectez-vous/'", $html);
        self::assertStringNotContainsString('Votre compte n\'existe pas', $html);
        self::assertStringNotContainsString("href='/creer-votre-compte'", $html);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame('1', get_user_meta(42, 'email-verfied', true));
        self::assertSame('', get_user_meta(42, '2fa_code_hash', true));
        self::assertSame('existing-password', get_userdata(42)->user_pass);
        self::assertSame('native-reset-key', get_userdata(42)->user_activation_key);
    }

    public function testResendForAnAbsentAccountStillPointsToSignup(): void
    {
        $html = $this->render('page-verifier-votre-email.php', ['user' => self::EMAIL], ['form_action' => '2FA_send_code']);

        self::assertStringContainsString("href='/creer-votre-compte'", $html);
        self::assertStringContainsString('Votre compte n\'existe pas', $html);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame([], $GLOBALS['__phpunit_inserted_users']);
    }

    public function testResendForAnEligibleSubscriberStillSendsACode(): void
    {
        $GLOBALS['__phpunit_users'][42] = new WP_User(['subscriber'], 42, self::EMAIL);

        $html = $this->render('page-verifier-votre-email.php', ['user' => self::EMAIL], ['form_action' => '2FA_send_code']);

        self::assertStringContainsString('Nous avons bien reçu votre demande de code', $html);
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        $mail = $GLOBALS['__phpunit_sent_mails'][0];
        self::assertSame(self::EMAIL, $mail['to']);
        self::assertTrue(is_2fa_code_valid(42, json_decode($mail['t:variables'], true)['code']));
    }

    public static function deniedContacts(): iterable
    {
        $responses = apexSearchFixture();
        yield 'provider absence' => [$responses['absent']];
        yield 'known ineligible contact' => [$responses['ineligible']];
        yield 'ineligible contact with nullable ancillary flags' => [array_replace($responses['ineligible'], [
            'isAncienMembre' => null,
            'hasMandatActif' => null,
            'hasEspaceDon' => null,
        ])];
    }

    #[DataProvider('deniedContacts')]
    public function testSignupDeniesAbsentAndIneligibleContactsAsABusinessResult(array $response): void
    {
        $GLOBALS['__phpunit_wp_remote_response_queue'][0]['body'] = json_encode($response);

        $html = $this->render('page-creer-votre-compte.php', [], ['email' => self::EMAIL]);

        self::assertStringContainsString('ne trouve pas de correspondance', $html);
        self::assertStringNotContainsString(AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE, $html);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame([], $GLOBALS['__phpunit_inserted_users']);
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    #[DataProvider('deniedContacts')]
    public function testCodeResendDoesNotSendToAbsentOrIneligibleContacts(array $response): void
    {
        $GLOBALS['__phpunit_users'][42] = new WP_User(['subscriber'], 42, self::EMAIL);
        $GLOBALS['__phpunit_wp_remote_response_queue'][0]['body'] = json_encode($response);

        $html = $this->render('page-verifier-votre-email.php', ['user' => self::EMAIL], ['form_action' => '2FA_send_code']);

        self::assertStringContainsString('Utilisateur inconnu', $html);
        self::assertStringNotContainsString(AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE, $html);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    #[DataProvider('deniedContacts')]
    public function testLoginDoesNotAuthenticateAbsentOrIneligibleContacts(array $response): void
    {
        $GLOBALS['__phpunit_users'][42] = new WP_User(['subscriber'], 42, self::EMAIL);
        store_email_is_verified(42);
        $GLOBALS['__phpunit_wp_remote_response_queue'][0]['body'] = json_encode($response);

        $html = $this->render('page-connectez-vous.php', [], ['email' => self::EMAIL, 'password' => 'password']);

        self::assertStringContainsString('ne trouve pas de correspondance', $html);
        self::assertStringNotContainsString(AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE, $html);
        self::assertSame([], $GLOBALS['__phpunit_signons']);
        self::assertSame(0, get_current_user_id());
        self::assertSame('', get_user_meta(42, 'user_SF_ID', true));
    }

    public static function eligibleContacts(): iterable
    {
        $donor = apexSearchFixture()['donor'];
        yield 'eligible donor' => [$donor];
        yield 'eligible donor with nullable ancillary flags' => [array_replace($donor, [
            'isAncienMembre' => null,
            'hasMandatActif' => null,
            'hasEspaceDon' => null,
        ])];
    }

    #[DataProvider('eligibleContacts')]
    public function testLoginAuthenticatesVerifiedEligibleContacts(array $response): void
    {
        $user = new WP_User(['subscriber'], 42, self::EMAIL);
        $GLOBALS['__phpunit_users'][42] = $user;
        $GLOBALS['__phpunit_signon_result'] = $user;
        store_email_is_verified(42);
        $GLOBALS['__phpunit_wp_remote_response_queue'][0]['body'] = json_encode($response);

        $html = $this->render('page-connectez-vous.php', [], ['email' => self::EMAIL, 'password' => 'password']);

        self::assertSame('redirect:https://wordpress.example.test/mon-espace/', $html);
        self::assertSame([[
            'credentials' => ['user_login' => self::EMAIL, 'user_password' => 'password', 'remember' => true],
            'secure_cookie' => true,
        ]], $GLOBALS['__phpunit_signons']);
        self::assertSame(42, get_current_user_id());
        self::assertSame($response['Id'], get_user_meta(42, 'user_SF_ID', true));
    }

    public static function loginRedirects(): iterable
    {
        yield 'page of the site' => ['https://wordpress.example.test/mon-espace/recus-fiscaux/', 'https://wordpress.example.test/mon-espace/recus-fiscaux/'];
        yield 'relative path' => ['/mon-espace/recus-fiscaux/', '/mon-espace/recus-fiscaux/'];
        yield 'external site' => ['https://site-malveillant.example/session-expiree', 'https://wordpress.example.test/mon-espace/'];
        yield 'protocol-relative external site' => ['//site-malveillant.example/session-expiree', 'https://wordpress.example.test/mon-espace/'];
        yield 'external site behind credentials' => ['https://wordpress.example.test@site-malveillant.example/', 'https://wordpress.example.test/mon-espace/'];
        yield 'javascript scheme' => ['javascript:alert(1)', 'https://wordpress.example.test/mon-espace/'];
        yield 'empty value' => ['', 'https://wordpress.example.test/mon-espace/'];
    }

    #[DataProvider('loginRedirects')]
    public function testLoginOnlyFollowsRedirectToWithinTheSite(string $redirectTo, string $expected): void
    {
        $user = new WP_User(['subscriber'], 42, self::EMAIL);
        $GLOBALS['__phpunit_users'][42] = $user;
        $GLOBALS['__phpunit_signon_result'] = $user;
        store_email_is_verified(42);

        $result = $this->render('page-connectez-vous.php', ['redirect_to' => $redirectTo], ['email' => self::EMAIL, 'password' => 'password']);

        self::assertSame('redirect:' . $expected, $result);
        self::assertSame(42, get_current_user_id());
    }
}
