<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/rest_endpoint.php';

final class EmailRestEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['wpdb'] = new wpdb();
    }

    public function testRejectsAnInvalidEmailWith400(): void
    {
        $response = email_rest_endpoint(new WP_REST_Request(['email' => 'not-an-email']));

        self::assertInstanceOf(WP_Error::class, $response);
        self::assertSame('invalid_email', $response->get_error_code());
    }

    public function testReturnsExistsTrueWhenTheEmailIsAlreadyRegistered(): void
    {
        $GLOBALS['wpdb']->var_result = 1;

        $response = email_rest_endpoint(new WP_REST_Request(['email' => 'ada@example.test']));

        self::assertInstanceOf(WP_REST_Response::class, $response);
        self::assertSame(200, $response->get_status());
        self::assertTrue($response->get_data()['exists']);
        self::assertStringContainsString("WHERE email = 'ada@example.test'", $GLOBALS['wpdb']->calls[0]['query']);
    }

    public function testReturnsExistsFalseWhenTheEmailIsUnknown(): void
    {
        $GLOBALS['wpdb']->var_result = 0;

        $response = email_rest_endpoint(new WP_REST_Request(['email' => 'new@example.test']));

        self::assertFalse($response->get_data()['exists']);
    }
}
