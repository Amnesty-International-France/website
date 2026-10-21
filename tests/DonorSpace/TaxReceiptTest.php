<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

$posted_salesforce_data_donor_space = [];
$post_salesforce_data_donor_space_response = (object) ['success' => true];
$sf_user_ids = [];
$logged_salesforce_errors = [];
$tax_receipts_by_contact = [];
$tax_receipt_fetches = [];

if (!function_exists('get_salesforce_user_tax_reciept')) {
    function get_salesforce_user_tax_reciept(string $contactId): mixed
    {
        global $tax_receipts_by_contact, $tax_receipt_fetches;

        $tax_receipt_fetches[] = $contactId;

        return $tax_receipts_by_contact[$contactId] ?? [];
    }
}

if (!function_exists('aif_create_salesforce_invalid_response_error')) {
    function aif_create_salesforce_invalid_response_error(string $operation, string $endpoint): WP_Error
    {
        return new WP_Error('salesforce_invalid_response', 'Invalid response', [
            'operation' => $operation,
            'endpoint' => $endpoint,
        ]);
    }
}

function post_salesforce_data_donor_space(string $url, array $params = []): mixed
{
    global $posted_salesforce_data_donor_space, $post_salesforce_data_donor_space_response;

    $posted_salesforce_data_donor_space[] = ['url' => $url, 'params' => $params];

    return $post_salesforce_data_donor_space_response;
}

function aif_log_salesforce_error(WP_Error $error): void
{
    $GLOBALS['logged_salesforce_errors'][] = $error;
}

function get_SF_user_ID(int $user_id): string|false
{
    global $sf_user_ids;

    return $sf_user_ids[$user_id] ?? false;
}

require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';
require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/rest-controllers.php';
require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/2FA/index.php';

final class TaxReceiptTest extends TestCase
{
    protected function setUp(): void
    {
        global $posted_salesforce_data_donor_space, $post_salesforce_data_donor_space_response, $sf_user_ids,
            $tax_receipts_by_contact, $tax_receipt_fetches;

        $posted_salesforce_data_donor_space = [];
        $post_salesforce_data_donor_space_response = (object) ['success' => true];
        $sf_user_ids = [];
        $tax_receipts_by_contact = [];
        $tax_receipt_fetches = [];
        $GLOBALS['__phpunit_user_meta'] = [];
        $GLOBALS['__phpunit_users'] = [42 => new WP_User(['subscriber'], 42, 'member@example.test')];
        $GLOBALS['logged_salesforce_errors'] = [];
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

    private static function logInAsContact(string $contactId, array $receipts): void
    {
        $GLOBALS['sf_user_ids'][42] = $contactId;
        $GLOBALS['tax_receipts_by_contact'][$contactId] = $receipts;
        $GLOBALS['__phpunit_current_user_id'] = 42;
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidReferenceParams(): iterable
    {
        yield 'missing' => [[]];
        yield 'empty string' => [['taxReceiptReference' => '']];
        yield 'whitespace only' => [['taxReceiptReference' => "  \t "]];
        yield 'array' => [['taxReceiptReference' => ['REF-1']]];
        yield 'integer' => [['taxReceiptReference' => 123]];
    }

    #[DataProvider('invalidReferenceParams')]
    public function testHandleRequestRejectsAnInvalidTaxReceiptReference(array $params): void
    {
        self::logInAsContact('003-contact-id', [(object) ['Name' => 'REF-1'], (object) ['Name' => '123']]);

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request($params));

        self::assertSame(403, $response->get_status());
        self::assertSame('tax receipt ID not provided', $response->get_data()['message']);
        self::assertSame([], $GLOBALS['tax_receipt_fetches']);
        self::assertSame([], $GLOBALS['posted_salesforce_data_donor_space']);
    }

    public function testHandleRequestRejectsWhenUserHasNoSalesforceId(): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 999;

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(403, $response->get_status());
        self::assertSame('tax receipt ID not provided', $response->get_data()['message']);
        self::assertSame([], $GLOBALS['tax_receipt_fetches']);
        self::assertSame([], $GLOBALS['posted_salesforce_data_donor_space']);
    }

    public function testHandleRequestCreatesACaseForAReceiptTheCallerOwns(): void
    {
        self::logInAsContact('003-contact-id', [(object) ['Name' => 'REF-0'], (object) ['Name' => 'REF-1']]);

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(200, $response->get_status());
        self::assertSame('demand succeed', $response->get_data()['message']);
        self::assertSame(['003-contact-id'], $GLOBALS['tax_receipt_fetches']);
        self::assertCount(1, $GLOBALS['posted_salesforce_data_donor_space']);
        self::assertSame('003-contact-id', $GLOBALS['posted_salesforce_data_donor_space'][0]['params']['ContactId']);
        self::assertSame('REF-1', $GLOBALS['posted_salesforce_data_donor_space'][0]['params']['Identifiant__c']);
    }

    /**
     * @return iterable<string, array{array<int, mixed>, string}>
     */
    public static function receiptsNotContainingTheReference(): iterable
    {
        yield 'another donor\'s reference' => [[(object) ['Name' => 'REF-0']], 'REF-1'];
        yield 'no receipts' => [[], 'REF-1'];
        yield 'reference differing only by case or whitespace' => [[(object) ['Name' => 'ref-1'], (object) ['Name' => ' REF-1']], 'REF-1'];
        yield 'malformed records' => [['REF-1', (object) ['Name' => null], (object) ['Montant_recu__c' => 'REF-1']], 'REF-1'];
        // '1e1' == '10' under PHP's loose comparison of numeric strings.
        yield 'numeric string only loosely equal' => [[(object) ['Name' => '10']], '1e1'];
    }

    #[DataProvider('receiptsNotContainingTheReference')]
    public function testHandleRequestRejectsAReceiptTheCallerDoesNotOwn(array $receipts, string $reference): void
    {
        self::logInAsContact('003-contact-id', $receipts);

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => $reference]));

        self::assertSame(403, $response->get_status());
        self::assertSame('tax receipt not found', $response->get_data()['message']);
        self::assertSame([], $GLOBALS['posted_salesforce_data_donor_space']);
    }

    public function testHandleRequestReturns503WhenTheReceiptsFetchFails(): void
    {
        $error = new WP_Error('salesforce_transport_error', 'Request failed');
        self::logInAsContact('003-contact-id', []);
        $GLOBALS['tax_receipts_by_contact']['003-contact-id'] = $error;

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(503, $response->get_status());
        self::assertSame('service temporarily unavailable', $response->get_data()['message']);
        self::assertSame([$error], $GLOBALS['logged_salesforce_errors']);
        self::assertSame([], $GLOBALS['posted_salesforce_data_donor_space']);
    }

    public function testHandleRequestReturns503WhenTheReceiptsFetchReturnsANonArray(): void
    {
        self::logInAsContact('003-contact-id', []);
        $GLOBALS['tax_receipts_by_contact']['003-contact-id'] = (object) ['Name' => 'REF-1'];

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(503, $response->get_status());
        self::assertSame('service temporarily unavailable', $response->get_data()['message']);
        self::assertCount(1, $GLOBALS['logged_salesforce_errors']);
        self::assertSame('salesforce_invalid_response', $GLOBALS['logged_salesforce_errors'][0]->get_error_code());
        self::assertSame(
            ['operation' => 'GET', 'endpoint' => 'tax_receipts'],
            $GLOBALS['logged_salesforce_errors'][0]->get_error_data(),
        );
        self::assertSame([], $GLOBALS['posted_salesforce_data_donor_space']);
    }

    public function testHandleRequestReturns503WhenSalesforceFails(): void
    {
        global $post_salesforce_data_donor_space_response;

        $error = new WP_Error('salesforce_transport_error', 'Request failed');
        $post_salesforce_data_donor_space_response = $error;
        self::logInAsContact('003-contact-id', [(object) ['Name' => 'REF-1']]);

        $response = handle_duplicate_tax_receipt_request(new WP_REST_Request(['taxReceiptReference' => 'REF-1']));

        self::assertSame(503, $response->get_status());
        self::assertSame('service temporarily unavailable', $response->get_data()['message']);
        self::assertSame([$error], $GLOBALS['logged_salesforce_errors']);
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
        store_email_is_verified(42, 'member@example.test');

        $result = check_nonce(new WP_REST_Request([], ['X-WP-Nonce' => 'valid-nonce']));

        self::assertTrue($result);
    }

    public function testCheckNonceRejectsALoggedInUserWithAnUnverifiedEmail(): void
    {
        $GLOBALS['__phpunit_valid_nonces'] = ['valid-nonce'];
        $GLOBALS['__phpunit_current_user_id'] = 43;

        $result = check_nonce(new WP_REST_Request([], ['X-WP-Nonce' => 'valid-nonce']));

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('Email not verified.', $result->get_error_message());
    }
}
