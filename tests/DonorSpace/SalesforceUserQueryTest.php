<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// get_salesforce_data()/post_salesforce_data()/patch_salesforce_data() are
// stubbed once for the whole suite in tests/bootstrap.php (shared across
// domains that call them by these exact names, e.g. petitions and users).

// require_once: keeps this safe if another testsuite ever needs the same
// real file too (see the analogous note in SalesforcePetitionBulkCsvTest.php).
require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/salesforce/user.php';

final class SalesforceUserQueryTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__phpunit_salesforce_data_calls'] = [];
        $GLOBALS['__phpunit_salesforce_data_response'] = [];
    }

    private static function calls(): array
    {
        return $GLOBALS['__phpunit_salesforce_data_calls'];
    }

    public function testGetSalesforceUsersQueriesAllContacts(): void
    {
        get_salesforce_users();

        self::assertSame([['method' => 'GET', 'url' => URL . QUERY]], self::calls());
    }

    public function testGetSalesforceUserWithEmailBuildsAQuotedAndUrlEncodedWhereClause(): void
    {
        get_salesforce_user_with_email('ada@example.test');

        $expected = URL . QUERY . '+WHERE+Email=' . urlencode("'ada@example.test'");
        self::assertSame($expected, self::calls()[0]['url']);
    }

    public function testGetSalesforceUserWithEmailEscapesASingleQuoteInTheEmailBeforeQuoting(): void
    {
        // Documents current escaping approach (addslashes) for an email
        // containing a SOQL-significant single quote.
        get_salesforce_user_with_email("o'brien@example.test");

        $url = self::calls()[0]['url'];
        $whereClause = explode('+WHERE+Email=', $url)[1];

        self::assertSame("'o\\'brien@example.test'", urldecode($whereClause));
    }

    public function testPostSalesforceUsersTargetsTheContactSobject(): void
    {
        post_salesforce_users(['LastName' => 'Lovelace']);

        self::assertSame('services/data/v57.0/sobjects/Contact/', self::calls()[0]['url']);
        self::assertSame(['LastName' => 'Lovelace'], self::calls()[0]['params']);
    }

    public function testPostSalesforceActivistTargetsTheMilitantSobject(): void
    {
        post_salesforce_activist(['Email__c' => 'ada@example.test']);

        self::assertSame('services/data/v57.0/sobjects/Militant__c/', self::calls()[0]['url']);
    }

    public function testUpdateSalesforceUsersTargetsTheContactByIdAndPatches(): void
    {
        update_salesforce_users('003abc', ['MobilePhone' => '0102030405']);

        self::assertSame('PATCH', self::calls()[0]['method']);
        self::assertSame('services/data/v57.0/sobjects/Contact/003abc', self::calls()[0]['url']);
        self::assertSame(['MobilePhone' => '0102030405'], self::calls()[0]['params']);
    }
}
