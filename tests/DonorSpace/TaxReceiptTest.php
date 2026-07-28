<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

$posted_salesforce_data_donor_space = [];
$sf_user_ids = [];

function post_salesforce_data_donor_space(string $url, array $params = []): object
{
    global $posted_salesforce_data_donor_space;

    $posted_salesforce_data_donor_space[] = ['url' => $url, 'params' => $params];

    return (object) ['success' => true];
}

function get_SF_user_ID(int $user_id): string|false
{
    global $sf_user_ids;

    return $sf_user_ids[$user_id] ?? false;
}

require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';
require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/rest-controllers.php';

final class TaxReceiptTest extends TestCase
{
    protected function setUp(): void
    {
        global $posted_salesforce_data_donor_space, $sf_user_ids;

        $posted_salesforce_data_donor_space = [];
        $sf_user_ids = [];
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_valid_nonces'] = [];
    }

    private static function receipt(string $date): object
    {
        return (object) ['Date_emission__c' => $date];
    }

    public function testSortByDatePropOrdersMostRecentFirst(): void
    {
        $oldest = self::receipt('2022-03-01');
        $middle = self::receipt('2023-06-15');
        $newest = self::receipt('2024-01-10');

        $sorted = sortByDateProp([$middle, $oldest, $newest], 'Date_emission__c');

        self::assertSame([$newest, $middle, $oldest], $sorted);
    }

    public function testGroupByYearGroupsAndOrdersYearsDescending(): void
    {
        $y2022 = self::receipt('2022-03-01');
        $y2024a = self::receipt('2024-01-10');
        $y2024b = self::receipt('2024-11-20');
        $y2023 = self::receipt('2023-06-15');

        $grouped = groupByYear([$y2022, $y2024a, $y2024b, $y2023], 'Date_emission__c');

        // PHP casts numeric string array keys ("2024") to int automatically.
        self::assertSame([2024, 2023, 2022], array_keys($grouped));
        self::assertSame([$y2024a, $y2024b], $grouped[2024]);
        self::assertSame([$y2023], $grouped[2023]);
        self::assertSame([$y2022], $grouped[2022]);
    }

    public function testCreateDuplicateTaxReceiptRequestPostsExpectedSalesforcePayload(): void
    {
        global $posted_salesforce_data_donor_space;

        create_duplicate_taxt_receipt_request('003-contact-id', 'REF-123');

        self::assertCount(1, $posted_salesforce_data_donor_space);
        $call = $posted_salesforce_data_donor_space[0];

        self::assertSame('services/data/v57.0/sobjects/Case', $call['url']);
        self::assertSame('Envoi duplicata', $call['params']['Type_de_demande_AIF__c']);
        self::assertSame('003-contact-id', $call['params']['ContactId']);
        self::assertSame('REF-123', $call['params']['Identifiant__c']);
        self::assertSame(date('Y-m-d'), $call['params']['Date_de_la_demande__c']);
    }

    public function testHandleRequestRejectsMissingTaxReceiptReferenceButHttpStatusStaysDefault200(): void
    {
        global $sf_user_ids;

        $sf_user_ids[42] = '003-contact-id';
        $GLOBALS['__phpunit_current_user_id'] = 42;

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => '']));

        // Documents current (arguably buggy) behaviour: the body carries
        // 'status' => 403 but the actual WP_REST_Response HTTP status is left
        // at the default 200, since no explicit status is passed here.
        self::assertSame(200, $response->get_status());
        self::assertSame(403, $response->get_data()['status']);
        self::assertSame('tax receipt ID not provided', $response->get_data()['message']);
    }

    public function testHandleRequestRejectsWhenUserHasNoSalesforceId(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 999;

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(200, $response->get_status());
        self::assertSame(403, $response->get_data()['status']);
    }

    public function testHandleRequestReturns200OnSuccess(): void
    {
        global $sf_user_ids;

        $sf_user_ids[42] = '003-contact-id';
        $GLOBALS['__phpunit_current_user_id'] = 42;

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(200, $response->get_status());
        self::assertSame('demand succeed', $response->get_data()['message']);
    }

    public function testCheckNonceRejectsAnInvalidNonce(): void
    {
        $result = check_nonce(new WP_REST_Request([], ['X-WP-Nonce' => 'invalid']));

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('rest_forbidden', $result->get_error_code());
    }

    public function testCheckNonceRejectsAValidNonceWhenLoggedOut(): void
    {
        $GLOBALS['__phpunit_valid_nonces'] = ['valid-nonce'];
        $GLOBALS['__phpunit_current_user_id'] = 0;

        $result = check_nonce(new WP_REST_Request([], ['X-WP-Nonce' => 'valid-nonce']));

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('Not logged in.', $result->get_error_message());
    }

    public function testCheckNonceAcceptsAValidNonceWhenLoggedIn(): void
    {
        $GLOBALS['__phpunit_valid_nonces'] = ['valid-nonce'];
        $GLOBALS['__phpunit_current_user_id'] = 42;

        $result = check_nonce(new WP_REST_Request([], ['X-WP-Nonce' => 'valid-nonce']));

        self::assertTrue($result);
    }
}
