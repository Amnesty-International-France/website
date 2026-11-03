<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The donations pages print the donor's first name and the subject of their
 * Salesforce requests, typed by the donor, as text only, and the links of
 * their messages keep the whole URL.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class DonationPagesTest extends TestCase
{
    private const SCRIPT = '<script>alert(1)</script>';

    protected function setUp(): void
    {
        require_once __DIR__ . '/account-verification-page-stubs.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';

        if (!function_exists('aif_get_request_salesforce_member')) {
            function aif_get_request_salesforce_member(): object
            {
                return (object) ['Id' => '003000000000001', 'isMembre' => true, 'isDonateur' => false, 'hasMandatActif' => false];
            }
            function wp_get_current_user(): object
            {
                return (object) ['first_name' => $GLOBALS['__phpunit_first_name'] ?? 'Ada', 'user_email' => 'donor@example.test'];
            }
            function get_salesforce_user_data(string $id): object
            {
                return (object) (['Id' => $id, 'Identifiant_contact__c' => '123456'] + array_fill_keys([
                    'Salutation', 'FirstName', 'LastName', 'Adresse_Ligne_3__c', 'Adresse_Ligne_4__c', 'Adresse_Ligne_5__c',
                    'Code_Postal__c', 'Ville__c', 'Pays__c', 'HomePhone', 'MobilePhone',
                ], ''));
            }
            function aif_require_salesforce_records(mixed $result, string $endpoint): mixed
            {
                return $result;
            }
            function get_salesforce_user_SEPA_mandate(string $id): object
            {
                return (object) ['records' => []];
            }
            function get_active_sepa_mandate(array $records): ?object
            {
                return null;
            }
            function create_duplicate_update_IBAN_request(string $id, string $iban): object
            {
                return (object) ['success' => true];
            }
            function wp_nonce_field(string $action, string $name): void
            {
            }
            function aif_require_salesforce_object(mixed $result, string $endpoint, ?string $required_property = null): mixed
            {
                return $result;
            }
            function aif_get_user_status(object $sf_user): string
            {
                return 'membre';
            }
            function get_salesforce_user_demands(string $id): array
            {
                return $GLOBALS['__phpunit_demands'];
            }
            function aif_require_salesforce_array(mixed $result, string $endpoint): mixed
            {
                return $result;
            }
            function render_block(array $block): string
            {
                return '';
            }
        }
    }

    private function render(string $page): DOMXPath
    {
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $page;
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . ob_get_clean());

        return new DOMXPath($dom);
    }

    /**
     * @return list<string>
     */
    private static function hrefs(DOMXPath $xpath): array
    {
        return array_map(static fn (DOMAttr $href) => $href->value, iterator_to_array($xpath->query('//a/@href'), false));
    }

    public function testDonationsHomePrintsTheFirstNameAsText(): void
    {
        $GLOBALS['__phpunit_first_name'] = 'Ada' . self::SCRIPT;

        $xpath = $this->render('page-mes-dons.php');

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertSame('Bonjour Ada' . self::SCRIPT . ',', preg_replace('/\s+/', ' ', trim($xpath->query('//h1')->item(0)->textContent)));
    }

    public static function requestSubjects(): iterable
    {
        yield 'request type' => [['Type_de_demande_AIF__c' => 'Duplicata' . self::SCRIPT]];
        yield 'subject, without a request type' => [['Subject' => 'Reçu fiscal' . self::SCRIPT]];
    }

    #[DataProvider('requestSubjects')]
    public function testRequestsPrintTheSubjectAsTextAndEscapeTheContactLink(array $fields): void
    {
        $GLOBALS['__phpunit_demands'] = [(object) ($fields + ['Date_de_la_demande__c' => '2024-03-01', 'Statut_Espace_Don__c' => 'Traité'])];
        $GLOBALS['__phpunit_post_permalinks'][get_page_by_path('mon-espace/mes-dons/nous-contacter')->ID] = 'https://wordpress.example.test/nous-contacter/">' . self::SCRIPT;

        $xpath = $this->render('page-mes-demandes.php');
        $subject = $xpath->query('//p[@class="aif-my-demand-container__info-container__subject"]')->item(0);
        $contact = $xpath->query('//a[contains(@class, "btn--dark")]')->item(0);

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertSame(reset($fields), trim($subject->textContent));
        self::assertInstanceOf(DOMElement::class, $contact);
        self::assertStringStartsWith('https://wordpress.example.test/nous-contacter/', $contact->getAttribute('href'));
    }

    public function testFailedRequestLinkKeepsTheWholeContactUrl(): void
    {
        $GLOBALS['__phpunit_demands'] = [(object) ['Subject' => 'Duplicata', 'Date_de_la_demande__c' => '2024-03-01', 'Statut_Espace_Don__c' => 'Fermé - Echoué']];

        $hrefs = self::hrefs($this->render('page-mes-demandes.php'));

        self::assertContains("https://wordpress.example.test/mon-espace/mes-dons/nous-contacter/?subject=Ma%20demande%20n'a%20pas%20pu%20aboutir", $hrefs);
    }

    public function testPersonalInformationLinkKeepsTheWholeContactUrl(): void
    {
        $contact = seed_permalink_with_apostrophe('nous-contacter');

        $hrefs = self::hrefs($this->render('page-mes-informations-personnelles.php'));

        self::assertContains($contact . '?subject=Modifier%20mon%20e-mail', $hrefs);
    }

    public function testBankDetailsLinksKeepTheWholeRequestsUrl(): void
    {
        $requests = seed_permalink_with_apostrophe('mes-demandes');
        $GLOBALS['__phpunit_valid_nonces'] = ['iban-nonce'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['iban_nonce' => 'iban-nonce', 'iban' => 'FR76 3000 6000 0112 3456 7890 189'];

        $hrefs = self::hrefs($this->render('page-modification-coordonnees-bancaire.php'));

        self::assertCount(2, array_keys($hrefs, $requests, true));
    }
}
