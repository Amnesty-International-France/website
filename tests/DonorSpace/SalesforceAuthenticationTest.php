<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SalesforceAuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('AIF_SALESFORCE_URL')) {
            define('AIF_SALESFORCE_URL', 'https://salesforce.example.test/');
            define('AIF_SALESFORCE_CLIENT_ID', 'client-id');
            define('AIF_SALESFORCE_SECRET', 'client-secret');
        }

        if (!function_exists('wp_remote_post')) {
            function wp_remote_post(string $url, array $args = []): mixed
            {
                $GLOBALS['__phpunit_wp_remote_calls'][] = ['method' => 'POST', 'url' => $url, 'args' => $args];

                return $GLOBALS['__phpunit_wp_remote_response'];
            }
        }

        if (!function_exists('get_option')) {
            function get_option(string $key, mixed $default = false): mixed
            {
                return $GLOBALS['__phpunit_options'][$key] ?? $default;
            }

            function update_option(string $key, mixed $value): bool
            {
                $GLOBALS['__phpunit_options'][$key] = $value;

                return true;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/sales-force/authentification.php';

        $GLOBALS['__phpunit_options'] = [];
        $GLOBALS['__phpunit_wp_remote_calls'] = [];
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, '{"access_token":"token","issued_at":"1786370000000"}');
    }

    public function testTransportFailureReturnsExplicitError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = new WP_Error('http_request_failed', 'Connection timed out');

        $result = refresh_salesforce_token_donor_space();

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_transport_error', $result->get_error_code());
        self::assertSame('oauth_token', $result->get_error_data()['endpoint']);
        self::assertSame('http_request_failed', $result->get_error_data()['cause_code']);
    }

    public function testUnauthorizedResponseReturnsAuthenticationError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(401, '{"error":"invalid_client"}');

        $result = refresh_salesforce_token_donor_space();

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_authentication_error', $result->get_error_code());
        self::assertSame(401, $result->get_error_data()['status']);
    }

    public function testInvalidJsonReturnsExplicitError(): void
    {
        $GLOBALS['__phpunit_wp_remote_response'] = $this->response(200, '<html>Error</html>');

        $result = refresh_salesforce_token_donor_space();

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('salesforce_invalid_json', $result->get_error_code());
        self::assertArrayHasKey('json_error', $result->get_error_data());
    }

    public function testSuccessfulResponseCachesAndReturnsToken(): void
    {
        $result = refresh_salesforce_token_donor_space();

        self::assertSame('token', $result);
        self::assertSame('token', $GLOBALS['__phpunit_options']['salesforce_access_token']);
        self::assertArrayHasKey('salesforce_token_expiration_time', $GLOBALS['__phpunit_options']);
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
