<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tax receipt fields come from Salesforce and are printed as text, in HTML
 * attributes and in the JavaScript string of the duplicate button: each output
 * must decode back to the exact Salesforce value, without adding markup.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TaxReceiptsPageTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/account-verification-page-stubs.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';

        if (!function_exists('get_salesforce_user_tax_reciept')) {
            function aif_get_request_salesforce_member(): object
            {
                return (object) ['Id' => '003000000000001'];
            }
            function get_salesforce_user_tax_reciept(mixed $id): array
            {
                return $GLOBALS['__phpunit_tax_receipts'];
            }
            function aif_require_salesforce_array(mixed $result, string $endpoint): mixed
            {
                return $result;
            }
            function wp_json_encode(mixed $value): string|false
            {
                return json_encode($value);
            }
            function render_block(array $block): string
            {
                return '';
            }
        }
    }

    private function render(object $receipt): DOMXPath
    {
        $GLOBALS['__phpunit_tax_receipts'] = [$receipt];

        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/page-mes-recus-fiscaux.php';

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . ob_get_clean());

        return new DOMXPath($dom);
    }

    public static function receiptNames(): iterable
    {
        yield 'Salesforce reference' => ['RF-2024-0001'];
        yield 'markup and quotes' => ["RF-1'\"><script>alert(1)</script>"];
    }

    #[DataProvider('receiptNames')]
    public function testDuplicateButtonTargetsTheReceiptByItsExactName(string $name): void
    {
        $xpath = $this->render((object) [
            'Name' => $name,
            'Debut__c' => '2024-01-01',
            'Fin__c' => '2024-12-31',
            'Montant_recu__c' => '<b>150</b>',
        ]);
        $button = $xpath->query('//button[@data-id="get-duplicate-tax-receipt-button"]')->item(0);
        $ids = array_map(static fn (DOMElement $div) => $div->getAttribute('id'), iterator_to_array($xpath->query('//div[@id]')));

        self::assertSame(0, $xpath->query('//script | //b')->length);
        self::assertInstanceOf(DOMElement::class, $button);
        self::assertSame('createDuplicateTaxReceiptDemand(' . json_encode($name) . ');', $button->getAttribute('onclick'));
        self::assertSame('Demander votre duplicata pour le reçu fiscal numéro ' . $name, $button->getAttribute('aria-label'));
        self::assertContains('aif-success-message-' . $name, $ids);
        self::assertContains('aif-error-message-' . $name, $ids);
        self::assertSame('Numéro du reçu fiscal : ' . $name, $xpath->query('//p[starts-with(., "Numéro du reçu fiscal")]')->item(0)->textContent);
        self::assertSame('<b>150</b> €', $xpath->query('//span[@class="aif-text-bold"]')->item(0)->textContent);
    }
}
