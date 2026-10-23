<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

final class ContactFormHttpError extends RuntimeException
{
    public function __construct(public readonly int $status)
    {
        parent::__construct('Request stopped with HTTP ' . $status);
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class DonorContactPatternTest extends TestCase
{
    private string $log_file;
    private string|false $previous_log;

    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/DonorSpace/account-verification-page-stubs.php';
        if (!function_exists('get_query_var')) {
            function get_query_var(string $key, mixed $default = ''): mixed
            {
                return $GLOBALS['__phpunit_query_vars'][$key] ?? $default;
            }
            function wp_get_current_user(): WP_User
            {
                return $GLOBALS['__phpunit_current_user'];
            }
            function wp_die(mixed $message = '', mixed $title = '', array $args = []): never
            {
                throw new ContactFormHttpError((int) ($args['response'] ?? 500));
            }
            function wp_nonce_field(string $action, string $name): void
            {
                echo '<input type="hidden" name="' . $name . '" value="contact-nonce">';
            }
            function is_preview(): bool
            {
                return $GLOBALS['__phpunit_is_preview'];
            }
            function is_admin(): bool
            {
                return $GLOBALS['__phpunit_admin_editor'];
            }
            function wp_is_serving_rest_request(): bool
            {
                return $GLOBALS['__phpunit_rest_editor'];
            }
            function get_current_screen(): ?object
            {
                return $GLOBALS['__phpunit_admin_editor'] ? new class () {
                    public function is_block_editor(): bool
                    {
                        return true;
                    }
                } : null;
            }
            function get_the_ID(): int|false
            {
                return $GLOBALS['__phpunit_editor_post_id'];
            }
            function current_user_can(string $capability, mixed ...$args): bool
            {
                return 'edit_posts' === $capability ? $GLOBALS['__phpunit_can_edit_posts'] : $GLOBALS['__phpunit_can_preview'];
            }
            function get_queried_object_id(): int
            {
                return 12;
            }
            function current_action(): string
            {
                return 'init';
            }
            function apply_block_hooks_to_content(string $content, mixed ...$args): string
            {
                return $content;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/authorization.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/email-verification/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/contact/index.php';
        // Unmodified upstream registry from the WordPress 7.1.3 investigation.
        require_once __DIR__ . '/fixtures/wordpress-7.1.3-patterns-registry.php';

        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [];
        $GLOBALS['__phpunit_wp_remote_response'] = ['response' => ['code' => 200], 'body' => '{"Id":"contact-id","Tech_Lien_Mandat_Actif__c":null}'];
        $GLOBALS['__phpunit_mail_response'] = ['response' => ['code' => 200], 'body' => '{"success":true,"id":"new-case"}'];
        $GLOBALS['__phpunit_sent_mails'] = [];
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 42;
        $GLOBALS['__phpunit_current_user'] = new WP_User(['subscriber'], 42, 'donor@example.test');
        $GLOBALS['__phpunit_users'] = [42 => $GLOBALS['__phpunit_current_user']];
        store_email_is_verified(42);
        $GLOBALS['__phpunit_query_vars'] = [];
        $GLOBALS['__phpunit_is_preview'] = false;
        $GLOBALS['__phpunit_admin_editor'] = false;
        $GLOBALS['__phpunit_rest_editor'] = false;
        $GLOBALS['__phpunit_editor_post_id'] = 12;
        $GLOBALS['__phpunit_can_preview'] = false;
        $GLOBALS['__phpunit_can_edit_posts'] = false;
        $GLOBALS['__phpunit_valid_nonces'] = ['contact-nonce'];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->log_file = tempnam(sys_get_temp_dir(), 'aif-contact-log-');
        $this->previous_log = ini_set('error_log', $this->log_file);
    }

    protected function tearDown(): void
    {
        ini_set('error_log', (string) $this->previous_log);
        unlink($this->log_file);
    }

    private function registry(): WP_Block_Patterns_Registry
    {
        $registry = new WP_Block_Patterns_Registry();
        $registry->register('amnesty/contact-us', [
            'title' => 'Nous contacter',
            'inserter' => false,
            'filePath' => dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/contact-us.php',
        ]);

        return $registry;
    }

    private function prepareMember(bool $eligible = true): void
    {
        $GLOBALS['__phpunit_query_vars']['aif_salesforce_member'] = (object) [
            'Id' => $eligible ? 'donor-contact' : 'ineligible-contact',
            'isDonateur' => $eligible,
            'isMembre' => false,
            'isAncienMembre' => true,
            'hasMandatActif' => true,
            'hasEspaceDon' => true,
        ];
    }

    private function renderForm(): string
    {
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/full-site-editing/blocks/donor-contact-form/render.php';

        return render_donor_contact_form_block();
    }

    public static function catalogueRequests(): iterable
    {
        yield 'GET one' => ['GET', false];
        yield 'GET all' => ['GET', true];
        yield 'POST one' => ['POST', false];
        yield 'POST all' => ['POST', true];
    }

    #[DataProvider('catalogueRequests')]
    public function testCatalogueLoadsTheRealPatternWithoutContextOrSideEffects(string $method, bool $all): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_POST = ['subject' => 'Do not submit', 'message' => 'Catalogue request', 'contact_nonce' => 'contact-nonce'];
        $registry = $this->registry();

        $pattern = $all ? $registry->get_all_registered()[0] : $registry->get_registered('amnesty/contact-us');

        self::assertStringContainsString('amnesty-core/donor-contact-form', $pattern['content']);
        self::assertStringNotContainsString('name="contact_nonce"', $pattern['content']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame('', file_get_contents($this->log_file));
    }

    public function testCatalogueCachingDoesNotFreezeTheLaterAuthorizedForm(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Stale catalogue subject', 'message' => 'Do not submit', 'contact_nonce' => 'contact-nonce'];
        $registry = $this->registry();
        $catalogue = $registry->get_all_registered()[0];
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $_GET = ['subject' => "Demande d\\'un reçu fiscal"];
        $this->prepareMember();
        self::assertSame($catalogue['content'], $registry->get_registered('amnesty/contact-us')['content']);
        $html = $this->renderForm();

        self::assertStringContainsString('value="Demande d&#039;un reçu fiscal"', $html);
        self::assertStringContainsString('name="contact_nonce"', $html);
        self::assertStringNotContainsString('Stale catalogue subject', $html);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testAuthorizedPostCreatesOneRequestEvenIfRenderedTwice(): void
    {
        $this->prepareMember();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'My request', 'message' => 'My message', 'contact_nonce' => 'contact-nonce'];

        $first = $this->renderForm();
        $second = $this->renderForm();

        self::assertStringContainsString('Votre demande de contact à bien été prise en compte', $first);
        self::assertStringContainsString('Votre demande de contact à bien été prise en compte', $second);
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        $request = json_decode($GLOBALS['__phpunit_sent_mails'][0], true);
        self::assertSame('donor-contact', $request['ContactId']);
        self::assertSame('My request', $request['Subject']);
    }

    public static function invalidNonces(): iterable
    {
        yield 'missing' => [null];
        yield 'wrong' => ['wrong-nonce'];
    }

    #[DataProvider('invalidNonces')]
    public function testPostWithAnInvalidNonceStopsBeforeAnySalesforceCall(?string $nonce): void
    {
        $this->prepareMember();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Subject', 'message' => 'Message'];
        if (null !== $nonce) {
            $_POST['contact_nonce'] = $nonce;
        }

        try {
            $this->renderForm();
            self::fail('Invalid nonce must stop the request.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(403, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testMissingContextOnTheActualFormIsStillAServiceFailure(): void
    {
        try {
            $this->renderForm();
            self::fail('A missing render context must not silently succeed.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(503, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testVisitorCannotRenderTheFormEvenWithAPreparedContext(): void
    {
        $this->prepareMember();
        $GLOBALS['__phpunit_current_user_id'] = 0;
        try {
            $this->renderForm();
            self::fail('Visitors must not access a donor contact form.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(403, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testAuthorizedPreviewNeedsNoMemberAndDoesNotSubmit(): void
    {
        $GLOBALS['__phpunit_is_preview'] = true;
        $GLOBALS['__phpunit_can_preview'] = true;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Preview', 'message' => 'Do not submit', 'contact_nonce' => 'contact-nonce'];

        $html = $this->renderForm();

        self::assertStringContainsString('Nous contacter', $html);
        self::assertStringNotContainsString('<form', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testEditorRestPreloadNeedsNoDonorContextAndDoesNotSubmit(): void
    {
        $GLOBALS['__phpunit_admin_editor'] = true;
        $GLOBALS['__phpunit_can_preview'] = true;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Editor preload', 'message' => 'Do not submit', 'contact_nonce' => 'contact-nonce'];

        $html = $this->renderForm();

        self::assertStringContainsString('Nous contacter', $html);
        self::assertStringNotContainsString('<form', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public static function editorPostContexts(): iterable
    {
        yield 'with a page' => [12, true, false];
        yield 'autoRegister without a page' => [false, false, true];
    }

    #[DataProvider('editorPostContexts')]
    public function testAuthorizedServerSideRenderNeedsNoContextAndDoesNotSubmit(int|false $post_id, bool $can_edit_post, bool $can_edit_posts): void
    {
        $GLOBALS['__phpunit_rest_editor'] = true;
        $GLOBALS['__phpunit_editor_post_id'] = $post_id;
        $GLOBALS['__phpunit_can_preview'] = $can_edit_post;
        $GLOBALS['__phpunit_can_edit_posts'] = $can_edit_posts;
        $_GET = ['context' => 'edit'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'REST preview', 'message' => 'Do not submit', 'contact_nonce' => 'contact-nonce'];

        $html = $this->renderForm();

        self::assertStringContainsString('Le formulaire est disponible dans votre espace donateur.', $html);
        self::assertStringNotContainsString('<form', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public static function nonEditorRequests(): iterable
    {
        yield 'frontend spoofing edit context' => [false, 'edit', true, 12, true];
        yield 'REST view context' => [true, 'view', true, false, true];
        yield 'REST without edit permission' => [true, 'edit', false, 12, false];
        yield 'REST page without specific permission' => [true, 'edit', false, 12, true];
        yield 'REST without a page or permission' => [true, 'edit', false, false, false];
    }

    #[DataProvider('nonEditorRequests')]
    public function testNonEditorRequestsStillRequireTheDonorContext(bool $rest, string $context, bool $can_edit, int|false $post_id, bool $can_edit_posts): void
    {
        $GLOBALS['__phpunit_rest_editor'] = $rest;
        $GLOBALS['__phpunit_can_preview'] = $can_edit;
        $GLOBALS['__phpunit_editor_post_id'] = $post_id;
        $GLOBALS['__phpunit_can_edit_posts'] = $can_edit_posts;
        $_GET = ['context' => $context];

        try {
            $this->renderForm();
            self::fail('The request must not receive an editor preview.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(503, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public static function subjects(): iterable
    {
        yield 'markup' => ['"><script>alert(1)</script>', '&quot;&gt;alert(1)'];
        yield 'apostrophe' => ["Demande d\\'un reçu fiscal", 'Demande d&#039;un reçu fiscal'];
        yield 'array' => [['subject'], ''];
    }

    #[DataProvider('subjects')]
    public function testAuthorizedFormEscapesTheSubject(mixed $subject, string $expected): void
    {
        $this->prepareMember();
        $_GET = ['subject' => $subject];

        $html = $this->renderForm();

        self::assertStringContainsString('value="' . $expected . '"', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    public function testUnverifiedSubscriberCannotSubmitEvenWithAValidNonce(): void
    {
        $this->prepareMember();
        $GLOBALS['__phpunit_user_meta'] = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Subject', 'message' => 'Message', 'contact_nonce' => 'contact-nonce'];

        try {
            $this->renderForm();
            self::fail('An unverified account must not access the form.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(403, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testIneligibleContextCannotRenderTheForm(): void
    {
        $this->prepareMember(false);

        try {
            $this->renderForm();
            self::fail('An ineligible contact must not access the form.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(403, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testRejectedSubmissionKeepsItsErrorWhenRenderedTwice(): void
    {
        $this->prepareMember();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Subject', 'message' => 'Message', 'contact_nonce' => 'contact-nonce'];
        $GLOBALS['__phpunit_mail_response']['body'] = '{"success":false}';

        self::assertStringContainsString("Votre demande n'a pas pu aboutir", $this->renderForm());
        self::assertStringContainsString("Votre demande n'a pas pu aboutir", $this->renderForm());
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
    }

    public function testTechnicalSubmissionFailureStillStopsWith503WithoutRetryingTheCase(): void
    {
        $this->prepareMember();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['subject' => 'Subject', 'message' => 'Message', 'contact_nonce' => 'contact-nonce'];
        $GLOBALS['__phpunit_mail_response'] = new WP_Error('http_request_failed', 'Transport failure');

        for ($render = 0; $render < 2; ++$render) {
            try {
                $this->renderForm();
                self::fail('A technical error must not silently succeed.');
            } catch (ContactFormHttpError $error) {
                self::assertSame(503, $error->status);
            }
        }
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
    }
}
