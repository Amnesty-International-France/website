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
            function set_query_var(string $key, mixed $value): void
            {
                $GLOBALS['__phpunit_query_vars'][$key] = $value;
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
            function wp_is_rest_endpoint(): bool
            {
                return $GLOBALS['__phpunit_rest_endpoint'];
            }
            function is_singular(): bool
            {
                return $GLOBALS['__phpunit_is_singular'];
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
            function locate_template(string $path): string
            {
                $GLOBALS['__phpunit_located_template'] = $path;

                return $GLOBALS['__phpunit_template_override'] ?? dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $path;
            }
            function nocache_headers(): void
            {
            }
            function sanitize_textarea_field(string $value): string
            {
                return trim(strip_tags($value));
            }
            function esc_textarea(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            function wp_safe_redirect(string $location, int $status = 302): never
            {
                $GLOBALS['__phpunit_redirect_status'] = $status;
                wp_redirect($location, $status);
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/authorization.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/contact/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/full-site-editing/blocks/donor-contact-form/submission.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/full-site-editing/blocks/donor-contact-form/render.php';
        // Unmodified upstream registry from the WordPress 7.1.3 investigation.
        require_once __DIR__ . '/fixtures/wordpress-7.1.3-patterns-registry.php';

        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [];
        $GLOBALS['__phpunit_wp_remote_response'] = ['response' => ['code' => 200], 'body' => '{"Id":"contact-id","Tech_Lien_Mandat_Actif__c":null}'];
        $GLOBALS['__phpunit_mail_response'] = ['response' => ['code' => 200], 'body' => '{"success":true,"id":"new-case"}'];
        $GLOBALS['__phpunit_sent_mails'] = [];
        $GLOBALS['__phpunit_query_vars'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 42;
        $GLOBALS['__phpunit_is_preview'] = false;
        $GLOBALS['__phpunit_admin_editor'] = false;
        $GLOBALS['__phpunit_rest_endpoint'] = false;
        $GLOBALS['__phpunit_is_singular'] = true;
        $GLOBALS['__phpunit_editor_post_id'] = 12;
        $GLOBALS['__phpunit_can_preview'] = false;
        $GLOBALS['__phpunit_can_edit_posts'] = false;
        $GLOBALS['__phpunit_valid_nonces'] = ['contact-nonce'];
        $GLOBALS['__phpunit_post_permalinks'][12] = 'https://wordpress.example.test/mon-espace/nous-contacter/';
        unset($GLOBALS['__phpunit_template_override']);
        $_GET = $_POST = [];
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

    private function prepareMember(): void
    {
        // auth_my_space() only stores a member after the shared access checks.
        set_query_var('aif_salesforce_member', (object) ['Id' => 'donor-contact']);
    }

    private function preparePost(array $fields = []): void
    {
        $this->prepareMember();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array_replace([
            'aif_donor_contact_form' => '1',
            'contact_nonce' => 'contact-nonce',
            'subject' => 'My request',
            'message' => 'My message',
        ], $fields);
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
        $this->preparePost();
        $_SERVER['REQUEST_METHOD'] = $method;
        $registry = $this->registry();
        $pattern = $all ? $registry->get_all_registered()[0] : $registry->get_registered('amnesty/contact-us');

        self::assertStringContainsString('amnesty-core/donor-contact-form', $pattern['content']);
        self::assertStringNotContainsString('name="contact_nonce"', $pattern['content']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame('', file_get_contents($this->log_file));
    }

    public function testCatalogueCachingDoesNotFreezeTheLaterFormAndGetDoesNotCallSalesforce(): void
    {
        $registry = $this->registry();
        $catalogue = $registry->get_all_registered()[0];
        $this->prepareMember();
        $_GET = ['subject' => "Demande d\\'un reçu fiscal"];
        $GLOBALS['__phpunit_wp_remote_response'] = new WP_Error('http_request_failed', 'Service unavailable');
        aif_handle_donor_contact_form();
        $html = render_donor_contact_form_block();

        self::assertSame($catalogue['content'], $registry->get_registered('amnesty/contact-us')['content']);
        self::assertStringContainsString('value="Demande d&#039;un reçu fiscal"', $html);
        self::assertStringContainsString('name="contact_nonce"', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testPostRedirectsBeforeRenderingAndPreservesMultilineMessage(): void
    {
        $this->preparePost(['subject' => "Demande d\\'un reçu", 'message' => "First paragraph\n\nSecond paragraph"]);
        try {
            aif_handle_donor_contact_form();
            self::fail('A successful POST must redirect before rendering.');
        } catch (AccountVerificationRedirect $redirect) {
            self::assertSame('https://wordpress.example.test/mon-espace/nous-contacter/?contact_sent=1', $redirect->getMessage());
            self::assertSame(303, $GLOBALS['__phpunit_redirect_status']);
        }
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        $request = json_decode($GLOBALS['__phpunit_sent_mails'][0], true);
        self::assertSame('donor-contact', $request['ContactId']);
        self::assertSame("Demande d'un reçu", $request['Subject']);
        self::assertSame("First paragraph\n\nSecond paragraph", $request['Description']);

        // The redirected GET and repeated renders never create another Case.
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $_GET = ['contact_sent' => '1'];
        set_query_var('aif_donor_contact_form', []);
        aif_handle_donor_contact_form();
        $first = render_donor_contact_form_block();
        self::assertSame($first, render_donor_contact_form_block());
        self::assertStringContainsString('Votre demande de contact a bien été prise en compte', $first);
        self::assertStringContainsString('https://wordpress.example.test/mon-espace/mes-dons/mes-demandes/', $first);
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testRenderingPostTwiceNeverSubmitsIt(): void
    {
        $this->preparePost();
        self::assertStringContainsString('<form', render_donor_contact_form_block());
        self::assertStringContainsString('<form', render_donor_contact_form_block());
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testAnUnrelatedPostDoesNotSubmitAContactRequest(): void
    {
        $this->preparePost();
        unset($_POST['aif_donor_contact_form']);
        aif_handle_donor_contact_form();

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
    public function testQuerySubjectIsSafelyReflected(mixed $subject, string $expected): void
    {
        $this->prepareMember();
        $_GET = ['subject' => $subject];

        $html = render_donor_contact_form_block();

        self::assertStringContainsString('value="' . $expected . '"', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public static function invalidNonces(): iterable
    {
        yield 'missing' => [null];
        yield 'wrong' => ['wrong-nonce'];
        yield 'array' => [['contact-nonce']];
    }

    #[DataProvider('invalidNonces')]
    public function testInvalidNonceStopsBeforeSalesforce(mixed $nonce): void
    {
        $this->preparePost(['contact_nonce' => $nonce]);
        try {
            aif_handle_donor_contact_form();
            self::fail('Invalid nonce must stop the request.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(403, $error->status);
        }
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public static function invalidFields(): iterable
    {
        yield 'missing subject' => [null, 'Message', 'subject'];
        yield 'empty subject' => ['', 'Message', 'subject'];
        yield 'blank subject' => ['   ', 'Message', 'subject'];
        yield 'array subject' => [['x'], 'Message', 'subject'];
        yield 'too long' => [str_repeat('é', 256), 'Message', 'subject'];
        yield 'missing message' => ['Subject', null, 'message'];
        yield 'empty message' => ['Subject', '', 'message'];
        yield 'blank message' => ['Subject', " \n\t ", 'message'];
        yield 'array message' => ['Subject', ['x'], 'message'];
    }

    #[DataProvider('invalidFields')]
    public function testInvalidFieldsAreRejectedBeforeSalesforce(mixed $subject, mixed $message, string $field): void
    {
        $this->preparePost(['subject' => $subject, 'message' => $message]);
        aif_handle_donor_contact_form();
        $html = render_donor_contact_form_block();

        self::assertArrayHasKey($field, get_query_var('aif_donor_contact_form')['errors']);
        self::assertStringContainsString('Veuillez vérifier les champs du formulaire.', $html);
        self::assertStringNotContainsString('a bien été prise en compte', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testSubjectLimitCountsCharactersInsteadOfUtf8Bytes(): void
    {
        $this->preparePost(['subject' => str_repeat('é', 255)]);
        $this->expectException(AccountVerificationRedirect::class);
        aif_handle_donor_contact_form();
    }

    public static function indirectRenders(): iterable
    {
        yield 'anonymous' => [false, true, false, false];
        yield 'missing member context' => [true, true, false, false];
        yield 'search with a prepared context' => [true, false, false, true];
        yield 'REST with a prepared context' => [true, true, true, true];
    }

    #[DataProvider('indirectRenders')]
    public function testIndirectRendersRemainEmptyAndNeverSend(bool $logged_in, bool $singular, bool $rest, bool $member): void
    {
        $this->preparePost();
        if (!$member) {
            set_query_var('aif_salesforce_member', null);
        }
        $GLOBALS['__phpunit_current_user_id'] = $logged_in ? 42 : 0;
        $GLOBALS['__phpunit_is_singular'] = $singular;
        $GLOBALS['__phpunit_rest_endpoint'] = $rest;
        aif_handle_donor_contact_form();

        self::assertSame('', render_donor_contact_form_block());
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public static function previews(): iterable
    {
        yield 'frontend page preview' => [false, false, true, 12, true, false];
        yield 'admin page editor' => [true, false, false, 12, true, false];
        yield 'admin editor without a post' => [true, false, false, false, false, true];
        yield 'REST with a page' => [false, true, false, 12, true, false];
        yield 'REST without a page' => [false, true, false, false, false, true];
        yield 'internal REST preload' => [true, true, false, false, false, true];
    }

    #[DataProvider('previews')]
    public function testPreviewIsStaticAndDoesNotSubmit(bool $admin, bool $rest, bool $preview, int|false $post_id, bool $edit_post, bool $edit_posts): void
    {
        $this->preparePost();
        $GLOBALS['__phpunit_admin_editor'] = $admin;
        $GLOBALS['__phpunit_rest_endpoint'] = $rest;
        $GLOBALS['__phpunit_is_preview'] = $preview;
        $GLOBALS['__phpunit_editor_post_id'] = $post_id;
        $GLOBALS['__phpunit_can_preview'] = $edit_post;
        $GLOBALS['__phpunit_can_edit_posts'] = $edit_posts;
        aif_handle_donor_contact_form();
        $html = render_donor_contact_form_block();

        self::assertStringContainsString('Le formulaire est disponible dans votre espace donateur.', $html);
        self::assertStringNotContainsString('<form', $html);
        self::assertSame([], $GLOBALS['__phpunit_wp_remote_calls']);
        self::assertSame([], $GLOBALS['__phpunit_sent_mails']);
    }

    public function testPreviewOfAPageRequiresItsSpecificEditPermission(): void
    {
        $GLOBALS['__phpunit_rest_endpoint'] = true;
        $GLOBALS['__phpunit_can_edit_posts'] = true;
        self::assertSame('', render_donor_contact_form_block());
    }

    public function testRejectedSubmissionPreservesEscapedValuesAndDoesNotSendAgainOnRender(): void
    {
        $this->preparePost(['subject' => '"><script>alert(1)</script>', 'message' => "First\n\n<textarea>Second</textarea>"]);
        $GLOBALS['__phpunit_mail_response']['body'] = '{"success":false}';
        aif_handle_donor_contact_form();
        $first = render_donor_contact_form_block();

        self::assertSame($first, render_donor_contact_form_block());
        self::assertStringContainsString("Votre demande n'a pas pu aboutir", $first);
        self::assertStringContainsString('value="&quot;&gt;alert(1)"', $first);
        self::assertStringContainsString("First\n\nSecond</textarea>", $first);
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
    }

    public function testTechnicalSubmissionFailureStopsWith503(): void
    {
        $this->preparePost();
        $GLOBALS['__phpunit_mail_response'] = new WP_Error('http_request_failed', 'Transport failure');
        try {
            aif_handle_donor_contact_form();
            self::fail('A technical error must not silently succeed.');
        } catch (ContactFormHttpError $error) {
            self::assertSame(503, $error->status);
        }
        self::assertCount(1, $GLOBALS['__phpunit_sent_mails']);
    }

    public function testFormUsesTheResolvedTemplate(): void
    {
        $this->prepareMember();
        $override = tempnam(sys_get_temp_dir(), 'aif-child-partial-');
        file_put_contents($override, '<?php echo "Child contact form";');
        $GLOBALS['__phpunit_template_override'] = $override;
        try {
            self::assertSame('Child contact form', render_donor_contact_form_block());
            self::assertSame('partials/my-space/contact-form.php', $GLOBALS['__phpunit_located_template']);
        } finally {
            unlink($override);
        }
    }
}
