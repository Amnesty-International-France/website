<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The petition fields pattern shows the values typed in a rejected tunnel signature,
 * and renders as before when it gets none.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PetitionFormFieldsPatternTest extends TestCase
{
    private const PATTERN = '/wp-content/themes/humanity-theme/patterns/petition-form-fields.php';
    private const TEXT_FIELDS = ['user_email', 'user_firstname', 'user_lastname', 'user_zipcode', 'user_phone'];

    protected function setUp(): void
    {
        require_once __DIR__ . '/stubs.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/signature-status.php';

        $GLOBALS['__phpunit_transients']['amnesty_fiche_pays_list'] = [(object) ['ID' => 1], (object) ['ID' => 2]];
        $GLOBALS['__phpunit_post_titles'] = [1 => 'Belgique', 2 => 'France'];
    }

    /**
     * Renders the pattern the way its includers do: with the variables in scope.
     *
     * @param array<string, mixed> $scope
     */
    private function render(array $scope): DOMXPath
    {
        extract($scope);
        ob_start();
        require dirname(__DIR__, 2) . self::PATTERN;
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . ob_get_clean(), LIBXML_NOERROR);

        return new DOMXPath($document);
    }

    private static function value(DOMXPath $html, string $name): ?string
    {
        $input = $html->query(sprintf('//input[@name="%s"]', $name))->item(0);

        return $input instanceof DOMElement && $input->hasAttribute('value') ? $input->getAttribute('value') : null;
    }

    /**
     * @return list<string>
     */
    private static function selected(DOMXPath $html, string $xpath): array
    {
        return array_map(
            static fn (DOMElement $node): string => $node->getAttribute('value'),
            iterator_to_array($html->query($xpath)),
        );
    }

    public function testTypedValuesAreShownEscaped(): void
    {
        $html = $this->render(['form_values' => [
            'user_email' => '"><script>alert(1)</script>',
            'civility' => 'Mme',
            'user_firstname' => "D'Arc",
            'user_lastname' => 'Lovelace',
            'user_zipcode' => '75001',
            'user_country' => 'Belgique',
            'user_phone' => '0600000000',
        ]]);

        self::assertSame('"><script>alert(1)</script>', self::value($html, 'user_email'));
        self::assertSame(0, $html->query('//script')->length);
        self::assertSame("D'Arc", self::value($html, 'user_firstname'));
        self::assertSame('Lovelace', self::value($html, 'user_lastname'));
        self::assertSame('75001', self::value($html, 'user_zipcode'));
        self::assertSame('0600000000', self::value($html, 'user_phone'));
        self::assertSame(['Mme'], self::selected($html, '//input[@name="civility"][@checked]'));
        self::assertSame(['Belgique'], self::selected($html, '//select[@name="user_country"]/option[@selected]'));
    }

    #[DataProvider('withoutTypedValues')]
    public function testWithoutTypedValuesFieldsAreEmptyWithDefaultChoices(array $scope): void
    {
        $html = $this->render($scope);

        foreach (self::TEXT_FIELDS as $name) {
            self::assertSame('', self::value($html, $name), $name);
        }
        self::assertSame(['M.'], self::selected($html, '//input[@name="civility"][@checked]'));
        self::assertSame(['France'], self::selected($html, '//select[@name="user_country"]/option[@selected]'));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function withoutTypedValues(): iterable
    {
        yield 'petition page include' => [[]];
        yield 'nothing remembered' => [['form_values' => []]];
        yield 'unknown country' => [['form_values' => ['user_country' => 'Atlantide']]];
        yield 'empty civility from the signed-in form' => [['form_values' => ['civility' => '']]];
    }

    public function testKeptCountryMatchesItsTexturizedTitle(): void
    {
        // get_the_title() texturizes the apostrophe; the browser posts it decoded.
        $GLOBALS['__phpunit_post_titles'][1] = 'Côte d&#8217;Ivoire';

        $html = $this->render(['form_values' => ['user_country' => 'Côte d’Ivoire']]);

        $selected = self::selected($html, '//select[@name="user_country"]/option[@selected]');
        self::assertCount(1, $selected);
        self::assertStringContainsString('Ivoire', $selected[0]);
    }

    public function testCivilityOutsideTheRadiosChecksNone(): void
    {
        $html = $this->render(['form_values' => ['civility' => 'Dr']]);

        self::assertSame([], self::selected($html, '//input[@name="civility"][@checked]'));
    }

    public function testEmailInputCarriesTheServerInvalidEmailMessage(): void
    {
        $input = $this->render([])->query('//input[@name="user_email"]')->item(0);

        self::assertInstanceOf(DOMElement::class, $input);
        self::assertSame(
            'Merci de renseigner une adresse email valide avant de signer.',
            $input->getAttribute('data-invalid-message'),
        );
    }

    public function testIncluderCivilityIsKeptWithoutTypedCivility(): void
    {
        $html = $this->render(['civility' => 'Autre']);

        self::assertSame(['Autre'], self::selected($html, '//input[@name="civility"][@checked]'));
    }
}
