<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

final class SalesforceServiceUnavailableTestException extends RuntimeException
{
    public function __construct(public readonly int $status)
    {
        parent::__construct('Salesforce service unavailable');
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SalesforceMemberDataTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('AIF_SALESFORCE_URL')) {
            define('AIF_SALESFORCE_URL', 'https://salesforce.example.test/');
        }

        if (!function_exists('get_salesforce_access_token_donor_space_donor_space')) {
            function get_salesforce_access_token_donor_space_donor_space(): mixed
            {
                return $GLOBALS['__phpunit_donor_space_access_token'];
            }
        }

        if (!function_exists('wp_remote_post')) {
            function wp_remote_post(string $url, array $args = []): mixed
            {
                $GLOBALS['__phpunit_wp_remote_calls'][] = ['method' => 'POST', 'url' => $url, 'args' => $args];

                return __phpunit_next_queued_response('__phpunit_wp_remote_response_queue', '__phpunit_wp_remote_response');
            }
        }

        if (!function_exists('set_query_var')) {
            function set_query_var(string $key, mixed $value): void
            {
                $GLOBALS['__phpunit_query_vars'][$key] = $value;
            }

            function get_query_var(string $key, mixed $default = ''): mixed
            {
                return $GLOBALS['__phpunit_query_vars'][$key] ?? $default;
            }
        }

        if (!function_exists('wp_get_current_user')) {
            function wp_get_current_user(): object
            {
                return $GLOBALS['__phpunit_current_user'];
            }
        }

        if (!function_exists('home_url')) {
            function home_url(string $path = ''): string
            {
                return 'https://wordpress.example.test' . $path;
            }
        }

        if (!function_exists('add_query_arg')) {
            function add_query_arg(mixed ...$args): string
            {
                return (string) end($args);
            }
        }

        if (!function_exists('get_page_by_path')) {
            function get_page_by_path(string $path): object
            {
                return (object) ['ID' => 1, 'post_name' => $path];
            }
        }

        if (!function_exists('get_permalink')) {
            function get_permalink(object|int $post): string
            {
                return 'https://wordpress.example.test/connectez-vous/';
            }
        }

        if (!function_exists('add_filter')) {
            function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
            {
            }
        }

        if (!function_exists('amnesty_logo')) {
            function amnesty_logo(string $url): void
            {
            }
        }

        if (!function_exists('amnesty_nav')) {
            function amnesty_nav(string $location): void
            {
            }
        }

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return $GLOBALS['__phpunit_is_page'];
            }

            function is_preview(): bool
            {
                return $GLOBALS['__phpunit_is_preview'];
            }

            function is_singular(string $post_type = ''): bool
            {
                return in_array($post_type, $GLOBALS['__phpunit_singular_post_types'], true);
            }

            function get_queried_object(): object
            {
                return $GLOBALS['__phpunit_queried_object'];
            }

            function get_post_ancestors(object|int $post): array
            {
                return $GLOBALS['__phpunit_post_ancestors'];
            }

            function current_user_can(string $capability, mixed ...$args): bool
            {
                return ($GLOBALS['__phpunit_user_capabilities'][$capability] ?? null) === $args;
            }
        }

        if (!function_exists('wp_die')) {
            function wp_die(mixed $message = '', mixed $title = '', array $args = []): never
            {
                throw new SalesforceServiceUnavailableTestException((int) ($args['response'] ?? 500));
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/user-data.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/authorization.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/my-space/template.php';

        $GLOBALS['__phpunit_donor_space_access_token'] = 'access-token';
        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response_queue'] = [];
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, '{}');
        $GLOBALS['__phpunit_query_vars'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 1;
        $GLOBALS['__phpunit_current_user'] = (object) ['user_email' => 'member@example.test'];
        $GLOBALS['__phpunit_is_page'] = false;
        $GLOBALS['__phpunit_is_preview'] = false;
        $GLOBALS['__phpunit_singular_post_types'] = [];
        $GLOBALS['__phpunit_queried_object'] = (object) ['ID' => 42, 'post_name' => 'content'];
        $GLOBALS['__phpunit_post_ancestors'] = [];
        $GLOBALS['__phpunit_user_capabilities'] = [];
        $GLOBALS['wp'] = (object) ['request' => 'mon-espace/mes-dons'];
    }

    public function testTransportErrorIsReturnedWithoutOutput(): void
    {
        $transport_error = new WP_Error('http_request_failed', 'Connection timed out');
        $GLOBALS['__phpunit_wp_remote_response'] = $transport_error;

        ob_start();
        $result = get_salesforce_member_data('timeout@example.test');
        $output = ob_get_clean();

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_transport_error', $result->get_error_code());
        self::assertSame('http_request_failed', $result->get_error_data()['cause_code']);
        self::assertSame(WP_Error::class, $result->get_error_data()['cause_class']);
        self::assertSame('', $output);
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testHttpErrorReturnsWpErrorWithStatus(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(503, '{"message":"Unavailable"}');

        $result = get_salesforce_member_data('http-error@example.test');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_http_error', $result->get_error_code());
        self::assertSame('GET', $result->get_error_data()['operation']);
        self::assertSame('member_search', $result->get_error_data()['endpoint']);
        self::assertSame(503, $result->get_error_data()['status']);
        self::assertIsInt($result->get_error_data()['duration_ms']);
    }

    #[DataProvider('httpErrorStatusProvider')]
    public function testExpectedHttpErrorsRemainExplicit(int $status): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response($status, '{"message":"Request failed"}');

        $result = get_salesforce_member_data('http-error@example.test');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_http_error', $result->get_error_code());
        self::assertSame($status, $result->get_error_data()['status']);
    }

    /** @return iterable<string, array{int}> */
    public static function httpErrorStatusProvider(): iterable
    {
        yield 'unauthorized' => [401];
        yield 'not found' => [404];
        yield 'rate limited' => [429];
        yield 'server error' => [500];
    }

    public function testEmptyResponseReturnsWpError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, '');

        $result = get_salesforce_member_data('empty-response@example.test');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_empty_response', $result->get_error_code());
    }

    public function testInvalidJsonReturnsWpError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, '<html>Error</html>');

        $result = get_salesforce_member_data('invalid-json@example.test');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_invalid_json', $result->get_error_code());
    }

    public function testNullJsonReturnsWpError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, 'null');

        $result = get_salesforce_member_data('null-json@example.test');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_null_response', $result->get_error_code());
    }

    public function testPostTransportErrorIsReturnedWithoutOutput(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = new WP_Error('http_request_failed', 'Connection timed out');

        ob_start();
        $result = post_salesforce_data_donor_space('services/data/v57.0/sobjects/Case', []);
        $output = ob_get_clean();

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_transport_error', $result->get_error_code());
        self::assertSame('POST', $result->get_error_data()['operation']);
        self::assertSame('case', $result->get_error_data()['endpoint']);
        self::assertSame('', $output);
    }

    public function testPatchNoContentReturnsTrue(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(204, '');

        $result = patch_salesforce_data_donor_space('services/data/v57.0/sobjects/Contact/contact-id', []);

        self::assertTrue($result);
    }

    public function testAuthenticationErrorIsPropagatedWithoutRequest(): void
    {
        $authentication_error = new WP_Error('salesforce_authentication_error', 'Authentication failed');
        $GLOBALS['__phpunit_donor_space_access_token'] = $authentication_error;

        $result = get_salesforce_member_data('member@example.test');

        self::assertSame($authentication_error, $result);
        self::assertCount(0, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testMissingContactIdentifierStopsBeforeHttpRequest(): void
    {
        $result = get_salesforce_user_data('');

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_missing_identifier', $result->get_error_code());
        self::assertSame('contact', $result->get_error_data()['endpoint']);
        self::assertCount(0, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testSalesforceLogContextContainsOnlyTechnicalData(): void
    {
        $error = new WP_Error('salesforce_http_error', 'Request failed', [
            'operation' => 'GET',
            'endpoint' => 'member_search',
            'duration_ms' => 30001,
            'status' => 503,
            'body_size' => 42,
            'cause_code' => 'http_request_failed',
            'cause_class' => WP_Error::class,
            'json_error' => 'Syntax error',
            'email' => 'private@example.test',
            'token' => 'secret-token',
            'body' => '{"private":true}',
            'url' => 'https://salesforce.example.test/member/private@example.test',
        ]);

        $context = aif_get_salesforce_log_context($error);

        self::assertSame([
            'error_code' => 'salesforce_http_error',
            'error_class' => WP_Error::class,
            'operation' => 'GET',
            'endpoint' => 'member_search',
            'duration_ms' => 30001,
            'cause_code' => 'http_request_failed',
            'cause_class' => WP_Error::class,
            'http_status' => 503,
            'body_size' => 42,
            'json_error' => 'Syntax error',
        ], $context);
    }

    public function testSalesforceErrorLogFallbackIsStructuredAndAnonymized(): void
    {
        $log_file = tempnam(sys_get_temp_dir(), 'aif-salesforce-log-');
        $previous_log_file = ini_set('error_log', $log_file);
        $error = new WP_Error('salesforce_http_error', 'Request failed', [
            'operation' => 'GET',
            'endpoint' => 'member_search',
            'status' => 503,
            'email' => 'private@example.test',
        ]);

        try {
            aif_log_salesforce_error($error);
            $logged_message = file_get_contents($log_file);
        } finally {
            ini_set('error_log', (string) $previous_log_file);
            unlink($log_file);
        }

        self::assertIsString($logged_message);
        self::assertStringContainsString('salesforce_http_error', $logged_message);
        self::assertStringContainsString('"http_status":503', $logged_message);
        self::assertStringNotContainsString('private@example.test', $logged_message);
    }

    public function testAccessHelperRejectsErrorsAndIncompleteMembers(): void
    {
        $error = new WP_Error('salesforce_request_failed', 'Request failed');

        self::assertFalse(has_access_to_donation_space($error));
        self::assertFalse(has_access_to_donation_space(null));
        self::assertFalse(has_access_to_donation_space((object) []));

        $invalid_member = aif_validate_salesforce_member(null);
        self::assertInstanceOf(WP_Error::class, $invalid_member);
        self::assertSame('salesforce_invalid_response', $invalid_member->get_error_code());
    }

    public function testAuthorizedMemberIsSharedThroughTheRequestContext(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(
            200,
            '{"Id":"contact-id","isDonateur":true,"isMembre":false,"hasMandatActif":false}'
        );

        $sf_member = check_user_page_access();
        set_query_var('aif_salesforce_member', $sf_member);

        self::assertSame($sf_member, aif_get_request_salesforce_member());
        self::assertSame($sf_member, aif_get_request_salesforce_member());
        self::assertSame($sf_member, aif_get_request_salesforce_member());
        self::assertSame('donateur', aif_get_user_status($sf_member));
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testEmptyObjectIsAValidAbsentContact(): void
    {
        $sf_member = aif_validate_salesforce_member((object) []);

        self::assertInstanceOf(stdClass::class, $sf_member);
        self::assertTrue(aif_is_salesforce_contact_absent($sf_member));
        self::assertFalse(has_access_to_donation_space($sf_member));
    }

    public function testIncompleteMemberIsAnInvalidResponse(): void
    {
        $sf_member = aif_validate_salesforce_member((object) [
            'Id' => 'contact-id',
            'isDonateur' => true,
            'isMembre' => false,
        ]);

        self::assertInstanceOf(WP_Error::class, $sf_member);
        self::assertSame('salesforce_invalid_response', $sf_member->get_error_code());
    }

    public function testMemberWithAccessAndNoIdIsAnInvalidResponse(): void
    {
        $sf_member = aif_validate_salesforce_member((object) [
            'Id' => null,
            'isDonateur' => true,
            'isMembre' => false,
            'hasMandatActif' => false,
        ]);

        self::assertInstanceOf(WP_Error::class, $sf_member);
        self::assertSame('salesforce_invalid_response', $sf_member->get_error_code());
    }

    public function testMissingPreparedMemberStopsWithoutNetworkFallback(): void
    {
        $this->assertSalesforceServiceUnavailable(function (): void {
            aif_get_request_salesforce_member();
        });

        self::assertCount(0, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testEditorPreviewSkipsSalesforceAccessCheck(): void
    {
        $GLOBALS['__phpunit_is_page'] = true;
        $GLOBALS['__phpunit_is_preview'] = true;
        $GLOBALS['__phpunit_post_ancestors'] = [1];
        $GLOBALS['__phpunit_user_capabilities'] = ['edit_post' => [42]];
        $GLOBALS['__phpunit_wp_remote_response'] = new WP_Error('http_request_failed', 'Must not be called');

        auth_my_space();

        self::assertArrayNotHasKey('aif_salesforce_member', $GLOBALS['__phpunit_query_vars']);
        self::assertCount(0, $GLOBALS['__phpunit_wp_remote_calls']);

        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/my-space-sidebar.php';
        $sidebar = ob_get_clean();

        self::assertIsString($sidebar);
        self::assertStringContainsString('id="my-space-sidebar"', $sidebar);
        self::assertCount(0, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    #[DataProvider('mySpaceSingleProvider')]
    public function testMySpaceSinglePreparesMemberContext(string $post_type, string $query_var): void
    {
        $GLOBALS['__phpunit_singular_post_types'] = [$post_type];
        $GLOBALS['__phpunit_query_vars'][$query_var] = 1;
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(
            200,
            '{"Id":"contact-id","isDonateur":true,"isMembre":true,"hasMandatActif":false}'
        );

        auth_my_space();

        self::assertSame('contact-id', aif_get_request_salesforce_member()->Id);
        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    /** @return iterable<string, array{string, string}> */
    public static function mySpaceSingleProvider(): iterable
    {
        yield 'actuality' => ['actualities-my-space', 'unused'];
        yield 'training' => ['training', 'is_my_space_training'];
        yield 'petition' => ['petition', 'is_my_space_petition'];
    }

    public function testMemberLookupFailureStopsAccessWith503AndNoDependentCall(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = new WP_Error('http_request_failed', 'Connection timed out');

        $this->assertSalesforceServiceUnavailable(function (): void {
            check_user_page_access();
        });

        self::assertCount(1, $GLOBALS['__phpunit_wp_remote_calls']);
    }

    public function testInvalidDependentResponseStopsWith503BeforePropertyAccess(): void
    {
        $this->assertSalesforceServiceUnavailable(function (): void {
            aif_require_salesforce_records((object) ['records' => null], 'sepa_mandates');
        });
    }

    private function assertSalesforceServiceUnavailable(callable $callback): void
    {
        $log_file = tempnam(sys_get_temp_dir(), 'aif-salesforce-log-');
        $previous_log_file = ini_set('error_log', $log_file);

        try {
            $callback();
            self::fail('The Salesforce failure should stop the request.');
        } catch (SalesforceServiceUnavailableTestException $exception) {
            self::assertSame(503, $exception->status);
        } finally {
            ini_set('error_log', (string) $previous_log_file);
            unlink($log_file);
        }
    }

    /** @return array{body:string,response:array{code:int}} */
    private function response(int $status, string $body): array
    {
        return [
            'body' => $body,
            'response' => ['code' => $status],
        ];
    }
}
