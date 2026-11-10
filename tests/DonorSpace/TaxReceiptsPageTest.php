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
        require_once __DIR__ . '/salesforce-page-stubs.php';
        require_once __DIR__ . '/html-helpers.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';

        if (!function_exists('get_salesforce_user_tax_reciept')) {
            function get_salesforce_user_tax_reciept(mixed $id): array
            {
                return $GLOBALS['__phpunit_tax_receipts'];
            }
            function wp_json_encode(mixed $value): string|false
            {
                return json_encode($value);
            }
        }
    }

    private function render(object $receipt): DOMXPath
    {
        $GLOBALS['__phpunit_tax_receipts'] = [$receipt];

        return render_html(static fn () => require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/page-mes-recus-fiscaux.php');
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
        self::assertContains('aif-limit-message-' . $name, $ids);
        self::assertSame('Numéro du reçu fiscal : ' . $name, $xpath->query('//p[starts-with(., "Numéro du reçu fiscal")]')->item(0)->textContent);
        self::assertSame('<b>150</b> €', $xpath->query('//span[@class="aif-text-bold"]')->item(0)->textContent);
    }

    public function testAlertLinksKeepTheirWholeUrl(): void
    {
        $contact = seed_permalink_with_apostrophe('mon-espace/mes-dons/nous-contacter');
        $requests = seed_permalink_with_apostrophe('mon-espace/mes-dons/mes-demandes');

        $xpath = $this->render((object) ['Name' => 'RF-2024-0001', 'Debut__c' => '2024-01-01', 'Fin__c' => '2024-12-31', 'Montant_recu__c' => 150]);
        $hrefs = link_hrefs($xpath);
        $titled = $xpath->query('//a[@title]')->item(0);

        self::assertContains($contact . '?subject=Mes%20dons%20et%20adhésions', $hrefs);
        self::assertContains($requests, $hrefs);
        self::assertContains($contact . '?subject=Mes%20reçus%20fiscaux', $hrefs);
        self::assertInstanceOf(DOMElement::class, $titled);
        self::assertSame('contacter le service SMD d’Amnesty France', $titled->getAttribute('title'));
    }
}
