<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The petition page aside shows the message of a rejected signature, read from the
 * `signature_status` the signature handler adds to its redirect.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AsidePetitionStickyPatternTest extends TestCase
{
    private const THEME = '/wp-content/themes/humanity-theme';
    private const ERROR_MESSAGE = 'Une erreur est survenue pendant la signature. Veuillez réessayer.';
    private const TURNSTILE_MESSAGE = 'La vérification de sécurité a échoué. Veuillez réessayer.';

    protected function setUp(): void
    {
        if (!function_exists('get_the_ID')) {
            function get_the_ID(): int
            {
                return 101;
            }
        }
        require_once __DIR__ . '/stubs.php';
        require_once dirname(__DIR__) . '/Clh/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . self::THEME . '/includes/petitions/signature-status.php';

        $GLOBALS['__phpunit_partials'] = [];
        $GLOBALS['__phpunit_acf_field_values'][0]['date_de_fin'] = '2099-12-31';
        $GLOBALS['__phpunit_transients']['amnesty_fiche_pays_list'] = [(object) ['ID' => 1]];
        $GLOBALS['__phpunit_post_titles'] = [1 => 'France'];
    }

    private function render(): DOMXPath
    {
        ob_start();
        require dirname(__DIR__, 2) . self::THEME . '/patterns/aside-petition-sticky.php';
        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . ob_get_clean(), LIBXML_NOERROR);

        return new DOMXPath($document);
    }

    /**
     * @return list<array{string, array<string, mixed>}>
     */
    private static function alertCalls(): array
    {
        return array_values(array_filter(
            $GLOBALS['__phpunit_partials'],
            static fn (array $call): bool => 'alert' === $call[0],
        ));
    }

    /**
     * @return list<string>
     */
    private static function alertContents(): array
    {
        return array_map(static fn (array $call): string => $call[1]['content'] ?? '', self::alertCalls());
    }

    public function testRejectedSignatureShowsItsMessage(): void
    {
        $_GET['signature_status'] = 'error';

        $this->render();

        self::assertSame(
            [['alert', ['state' => 'error', 'title' => 'Une erreur est survenue', 'content' => self::ERROR_MESSAGE]]],
            self::alertCalls(),
        );
    }

    /**
     * @param array<string, mixed> $query
     */
    #[DataProvider('withoutKnownStatus')]
    public function testWithoutKnownStatusNoAlertIsShown(array $query): void
    {
        $_GET = $query;

        $html = $this->render();

        self::assertSame([], self::alertContents());
        self::assertSame(0, $html->query('//script')->length);
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function withoutKnownStatus(): iterable
    {
        yield 'no status' => [[]];
        yield 'empty status' => [['signature_status' => '']];
        yield 'unknown status' => [['signature_status' => 'unknown']];
        yield 'markup payload' => [['signature_status' => '"><script>alert(1)</script>']];
        yield 'array status' => [['signature_status' => ['error']]];
    }

    public function testExpiredPetitionShowsItsEndInsteadOfTheAlert(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][0]['date_de_fin'] = '2000-01-01';
        $_GET['signature_status'] = 'expired';

        $html = $this->render();

        self::assertSame([], self::alertContents());
        self::assertSame(1, $html->query('//div[@class="close-petition"]')->length);
    }

    public function testTurnstileFailureStillShowsItsAlert(): void
    {
        $GLOBALS['petition_turnstile_error_message'] = self::TURNSTILE_MESSAGE;

        $this->render();

        self::assertSame([self::TURNSTILE_MESSAGE], self::alertContents());
    }

    public function testTurnstileFailureAndRejectedSignatureShowBothAlerts(): void
    {
        $GLOBALS['petition_turnstile_error_message'] = self::TURNSTILE_MESSAGE;
        $_GET['signature_status'] = 'error';

        $this->render();

        self::assertSame([self::TURNSTILE_MESSAGE, self::ERROR_MESSAGE], self::alertContents());
    }
}
