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
        require_once __DIR__ . '/salesforce-page-stubs.php';
        require_once __DIR__ . '/html-helpers.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/tax-receipt/index.php';
        require_once dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/bank/iban-request.php';
        $GLOBALS['wpdb'] = new IbanLockWpdb();

        if (!function_exists('get_salesforce_user_demands')) {
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
            function get_salesforce_user_SEPA_mandate(string $id): object
            {
                return (object) ['records' => []];
            }
            function get_active_sepa_mandate(array $records): ?object
            {
                return $GLOBALS['__phpunit_active_mandate'] ?? null;
            }
            function create_duplicate_update_IBAN_request(string $id, string $iban): object
            {
                $GLOBALS['__phpunit_iban_requests'][] = $iban;

                return $GLOBALS['__phpunit_iban_request_response'] ?? (object) ['success' => true];
            }
            function wp_nonce_field(string $action, string $name): void
            {
            }
            function aif_get_user_status(object $sf_user): string
            {
                return 'membre';
            }
            function get_salesforce_user_demands(string $id): array|WP_Error
            {
                return $GLOBALS['__phpunit_demands'] ?? [];
            }
        }
    }

    private function render(string $page): DOMXPath
    {
        return render_html(static fn () => require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $page);
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

        $hrefs = link_hrefs($this->render('page-mes-demandes.php'));

        self::assertContains("https://wordpress.example.test/mon-espace/mes-dons/nous-contacter/?subject=Ma%20demande%20n'a%20pas%20pu%20aboutir", $hrefs);
    }

    public function testPersonalInformationLinkKeepsTheWholeContactUrl(): void
    {
        $contact = seed_permalink_with_apostrophe('nous-contacter');

        $hrefs = link_hrefs($this->render('page-mes-informations-personnelles.php'));

        self::assertContains($contact . '?subject=Modifier%20mon%20e-mail', $hrefs);
    }

    public function testBankDetailsLinksKeepTheWholeRequestsUrl(): void
    {
        $requests = seed_permalink_with_apostrophe('mes-demandes');
        $this->lockIbanRequest();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['iban_sent' => '1'];

        $hrefs = link_hrefs($this->render('page-modification-coordonnees-bancaire.php'));

        self::assertCount(2, array_keys($hrefs, $requests, true));
    }

    public static function sentRequests(): iterable
    {
        yield 'first request' => [[]];
        yield 'previous requests closed in Salesforce' => [[self::ibanDemand('Rejeté'), self::ibanDemand('Traité')]];
    }

    #[DataProvider('sentRequests')]
    public function testBankDetailsRequestRedirectsAfterSendingIt(array $demands): void
    {
        $GLOBALS['__phpunit_demands'] = $demands;
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');

        try {
            $this->render('page-modification-coordonnees-bancaire.php');
            self::fail('The bank details form should redirect after sending the request.');
        } catch (AccountVerificationRedirect $redirect) {
            self::assertStringEndsWith('?iban_sent=1', $redirect->getMessage());
        }

        self::assertSame(303, $GLOBALS['__phpunit_redirect_status']);
        self::assertSame(['FR7630006000011234567890189'], $GLOBALS['__phpunit_iban_requests']);
        // The lock keeps how many IBAN requests Salesforce knew before this one.
        self::assertStringStartsWith('INSERT IGNORE', self::lockQueries()[0]);
        self::assertStringContainsString("'_transient_aif_iban_request_lock_" . md5('003000000000001') . "', '" . count($demands) . "'", self::lockQueries()[0]);
    }

    public static function pendingRequests(): iterable
    {
        yield 'lock set, Case not in Salesforce yet' => [1, [self::ibanDemand('Traité')]];
        yield 'request still open in Salesforce' => [null, [self::ibanDemand('En cours de traitement')]];
        yield 'lock set, Salesforce unavailable' => [0, new WP_Error('salesforce', 'down')];
    }

    #[DataProvider('pendingRequests')]
    public function testBankDetailsRequestIsNotSentAgainWhilePending(?int $lockedWithRequests, array|WP_Error $demands): void
    {
        if (null !== $lockedWithRequests) {
            $this->lockIbanRequest($lockedWithRequests);
        }
        $GLOBALS['__phpunit_demands'] = $demands;
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame([], $GLOBALS['__phpunit_iban_requests']);
        self::assertSame([], self::lockQueries());
        self::assertStringContainsString('Votre demande a déjà été envoyée, elle est en cours de traitement.', $xpath->document->textContent);
        self::assertSame('disabled', $xpath->query('//button[@id="iban-submit"]/@disabled')->item(0)?->nodeName);
    }

    public function testBankDetailsLockIsReleasedOnceTheCaseShowsUpInSalesforce(): void
    {
        // Sent with no IBAN request in Salesforce; the Case is now there, already rejected.
        $this->lockIbanRequest(0);
        $GLOBALS['__phpunit_demands'] = [self::ibanDemand('Rejeté')];
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');
        $GLOBALS['wpdb']->query_results = [2, 2]; // DELETE of the old lock, then INSERT of the new one.

        try {
            $this->render('page-modification-coordonnees-bancaire.php');
            self::fail('The bank details form should send the request once the rejected one is closed.');
        } catch (AccountVerificationRedirect) {
        }

        self::assertStringStartsWith('DELETE FROM', self::lockQueries()[0]);
        self::assertSame(['FR7630006000011234567890189'], $GLOBALS['__phpunit_iban_requests']);
    }

    public function testBankDetailsRequestIsNotSentWhenAnotherSubmissionHoldsTheLock(): void
    {
        // Free when the page loads, then held by the other submission when claiming it.
        $GLOBALS['wpdb']->expiries = [null, (string) (time() + 600)];
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');
        $GLOBALS['wpdb']->query_results = [0];

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame([], $GLOBALS['__phpunit_iban_requests']);
        self::assertStringContainsString('Votre demande a déjà été envoyée, elle est en cours de traitement.', $xpath->document->textContent);
    }

    public function testBankDetailsRequestIsNotSentWhenSalesforceRequestsAreUnavailable(): void
    {
        $GLOBALS['__phpunit_demands'] = new WP_Error('salesforce', 'down');
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame([], $GLOBALS['__phpunit_iban_requests']);
        self::assertSame([], self::lockQueries());
        self::assertStringContainsString('Un problème technique est survenu.', $xpath->document->textContent);
        self::assertSame('FR76 3000 6000 0112 3456 7890 189', self::submittedIban($xpath));
    }

    public function testBankDetailsPageStillLoadsWhenSalesforceRequestsAreUnavailable(): void
    {
        $GLOBALS['__phpunit_demands'] = new WP_Error('salesforce', 'down');
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame(1, $xpath->query('//button[@id="iban-submit"][not(@disabled)]')->length);
    }

    public static function rejectedIbans(): iterable
    {
        yield 'empty' => ['   ', 'Veuillez renseigner un IBAN.', ''];
        yield 'identical to the current IBAN' => ['fr14 2004 1010 0505 0001 3m02 606', 'L’IBAN saisi est identique à votre IBAN actuel.', 'FR14 2004 1010 0505 0001 3M02 606'];
        yield 'wrong check digits' => ['FR76 3000 6000 0112 3456 7890 188', 'L’IBAN saisi n’est pas valide.', 'FR76 3000 6000 0112 3456 7890 188'];
        yield 'not an IBAN' => ['abc', 'L’IBAN saisi n’est pas valide.', 'ABC'];
    }

    #[DataProvider('rejectedIbans')]
    public function testBankDetailsRequestIsNotSentForARejectedIban(string $iban, string $error, string $submittedIban): void
    {
        $GLOBALS['__phpunit_active_mandate'] = self::mandate('FR1420041010050500013M02606');
        $this->postIban($iban);

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame([], $GLOBALS['__phpunit_iban_requests']);
        self::assertSame([], self::lockQueries());
        self::assertStringContainsString($error, $xpath->document->textContent);
        // The JS puts the donor's input back in the field; "Annuler" restores the current IBAN.
        self::assertSame($submittedIban, self::submittedIban($xpath));
        self::assertSame('FR14 2004 1010 0505 0001 3M02 606', $xpath->query('//input[@id="iban"]/@value')->item(0)?->nodeValue);
    }

    public function testBankDetailsLockIsReleasedWhenSalesforceDoesNotCreateTheRequest(): void
    {
        $GLOBALS['__phpunit_iban_request_response'] = (object) ['success' => false];
        $this->postIban('FR76 3000 6000 0112 3456 7890 189');

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertSame(['FR7630006000011234567890189'], $GLOBALS['__phpunit_iban_requests']);
        self::assertStringStartsWith('DELETE FROM', self::lockQueries()[1] ?? '');
        self::assertStringContainsString('Un problème technique est survenu.', $xpath->document->textContent);
        self::assertSame('FR76 3000 6000 0112 3456 7890 189', self::submittedIban($xpath));
    }

    public function testBankDetailsSuccessMessageNeedsAPendingRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['iban_sent' => '1'];

        $xpath = $this->render('page-modification-coordonnees-bancaire.php');

        self::assertStringNotContainsString('Votre demande de modification a bien été prise en compte', $xpath->document->textContent);
    }

    private function postIban(string $iban): void
    {
        $GLOBALS['__phpunit_iban_requests'] = [];
        $GLOBALS['__phpunit_valid_nonces'] = ['iban-nonce'];
        $GLOBALS['wpdb']->query_results = [2];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['iban_nonce' => 'iban-nonce', 'iban' => $iban];
    }

    /** Lock set when Salesforce knew $knownRequests IBAN requests. */
    private function lockIbanRequest(int $knownRequests = 0): void
    {
        $GLOBALS['wpdb']->expiry = (string) (time() + 600);
        $GLOBALS['wpdb']->count = (string) $knownRequests;
    }

    private static function submittedIban(DOMXPath $xpath): ?string
    {
        return $xpath->query('//input[@id="iban"]/@data-submitted-iban')->item(0)?->nodeValue;
    }

    /** @return list<string> INSERT, UPDATE and DELETE queries sent on the lock. */
    private static function lockQueries(): array
    {
        return array_values(array_map(
            static fn (array $call): string => $call['query'],
            array_filter($GLOBALS['wpdb']->calls, static fn (array $call): bool => 'query' === $call['method'])
        ));
    }

    private static function ibanDemand(string $status): object
    {
        return (object) ['Type_de_demande_AIF__c' => 'Changement IBAN', 'Statut_Espace_Don__c' => $status, 'Date_de_la_demande__c' => '2026-11-20'];
    }

    private static function mandate(string $iban): object
    {
        return (object) ['Tech_Iban__c' => $iban, 'Date_paiement_Avenir__c' => '2026-12-05', 'Periodicite__c' => 'Mensuelle', 'Montant__c' => '15'];
    }
}

/**
 * $wpdb answering the IBAN request lock reads: its expiry, then how many IBAN
 * requests Salesforce knew when it was set. $expiries is consumed first, one per read.
 */
class IbanLockWpdb extends wpdb
{
    public ?string $expiry = null;
    public ?string $count = null;
    /** @var array<int, ?string> */
    public array $expiries = [];

    public function get_var(string $query): mixed
    {
        $this->calls[] = ['method' => 'get_var', 'query' => $query];

        if (!str_contains($query, '_transient_timeout_')) {
            return $this->count;
        }

        return [] !== $this->expiries ? array_shift($this->expiries) : $this->expiry;
    }
}
