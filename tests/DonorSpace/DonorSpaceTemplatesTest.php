<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * The aif-donor-space templates print what the pages hand them (Salesforce
 * data, messages, URLs): each value must come out as the exact text or a safe
 * URL, never as markup. Messages may hold links, so those go through
 * wp_kses_post() instead.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class DonorSpaceTemplatesTest extends TestCase
{
    private const SCRIPT = '<script>alert(1)</script>';

    protected function setUp(): void
    {
        if (!defined('AIF_DONOR_SPACE_PATH')) {
            define('AIF_DONOR_SPACE_PATH', dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/');
        }
        require_once __DIR__ . '/escaping-stubs.php';
        require_once AIF_DONOR_SPACE_PATH . 'includes/utils.php';
        require_once AIF_DONOR_SPACE_PATH . 'configuration.php';

        if (!function_exists('get_template_directory_uri')) {
            function get_template_directory_uri(): string
            {
                return $GLOBALS['__phpunit_template_directory_uri'];
            }
            function verify_turnstile(): ?string
            {
                return 'invalid-input-response';
            }
            function turnstile_friendly_error(string $error): string
            {
                return $GLOBALS['__phpunit_turnstile_message'];
            }
            function aif_turnstile_site_key(): string
            {
                return 'site-key';
            }
            function get_header(): void
            {
            }
            function get_footer(): void
            {
            }
        }
        $GLOBALS['__phpunit_template_directory_uri'] = 'https://wordpress.example.test/theme';
    }

    private function dom(callable $render): DOMXPath
    {
        ob_start();
        $render();
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . ob_get_clean());

        return new DOMXPath($dom);
    }

    private function partial(string $name, array $args): DOMXPath
    {
        return $this->dom(static fn () => aif_include_partial($name, $args));
    }

    private function message(string $partial, string $field, string $value): DOMXPath
    {
        $args = ['state' => 'error', 'id' => 'message', 'title' => 'Titre', 'content' => 'Contenu', 'additional_content' => 'Complément'];
        $args[$field] = $value;

        return $this->partial($partial, $args);
    }

    public static function messageFields(): iterable
    {
        yield 'alert title' => ['alert', 'title'];
        yield 'alert content' => ['alert', 'content'];
        yield 'alert additional_content' => ['alert', 'additional_content'];
        yield 'info-message content' => ['info-message', 'content'];
    }

    #[DataProvider('messageFields')]
    public function testMessageKeepsLinks(string $partial, string $field): void
    {
        $xpath = $this->message($partial, $field, "Rendez-vous sur <a class='aif-link--primary' href='https://wordpress.example.test/creer-votre-compte/'>Créer mon compte</a>.");
        $link = $xpath->query('//a')->item(0);

        self::assertInstanceOf(DOMElement::class, $link);
        self::assertSame('https://wordpress.example.test/creer-votre-compte/', $link->getAttribute('href'));
        self::assertSame('aif-link--primary', $link->getAttribute('class'));
        self::assertSame('Créer mon compte', $link->textContent);
    }

    public static function activeContent(): iterable
    {
        foreach (self::messageFields() as $name => [$partial, $field]) {
            yield "{$name}: script" => [$partial, $field, 'Message' . self::SCRIPT, '//script'];
            yield "{$name}: event handler" => [$partial, $field, "<a href='https://wordpress.example.test/' onclick='alert(1)'>lien</a>", '//@*[starts-with(name(), "on")]'];
            yield "{$name}: javascript link" => [$partial, $field, "<a href='javascript:alert(1)'>lien</a>", '//a[starts-with(normalize-space(@href), "javascript:")]'];
        }
    }

    #[DataProvider('activeContent')]
    public function testMessageDropsActiveContent(string $partial, string $field, string $value, string $injected): void
    {
        self::assertSame(0, $this->message($partial, $field, $value)->query($injected)->length);
    }

    public function testInfoMessageEscapesItsId(): void
    {
        $id = 'help">' . self::SCRIPT;

        $xpath = $this->partial('info-message', ['id' => $id, 'content' => 'Contenu']);
        $container = $xpath->query('//div[@class="aif-info-message__container"]')->item(0);

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertInstanceOf(DOMElement::class, $container);
        self::assertSame($id, $container->getAttribute('id'));
    }

    public function testLabelPrintsItsContentAsText(): void
    {
        $content = 'Traité' . self::SCRIPT;

        $xpath = $this->partial('label', ['content' => $content, 'variant' => 'success']);

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertSame($content, trim($xpath->query('//p')->item(0)->textContent));
    }

    public static function navCardUrls(): iterable
    {
        yield 'markup in the URL' => ['/mon-espace/">' . self::SCRIPT];
        yield 'javascript scheme' => ['javascript:alert(1)'];
    }

    #[DataProvider('navCardUrls')]
    public function testNavCardEscapesItsLinkAndTexts(string $url): void
    {
        $title = 'Mes demandes' . self::SCRIPT;
        $content = 'Affichez vos demandes.' . self::SCRIPT;

        $xpath = $this->partial('nav-card', ['iconName' => 'plane', 'url' => $url, 'title' => $title, 'content' => $content]);
        $link = $xpath->query('//a')->item(0);

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertInstanceOf(DOMElement::class, $link);
        self::assertStringStartsNotWith('javascript:', $link->getAttribute('href'));
        self::assertSame($title, $xpath->query('//h2')->item(0)->textContent);
        self::assertSame($content, trim($xpath->query('//p')->item(0)->textContent));
    }

    public static function bannerMembers(): iterable
    {
        yield 'donor with an active mandate' => [(object) ['hasMandatActif' => true, 'isMembre' => false]];
        yield 'member' => [(object) ['hasMandatActif' => false, 'isMembre' => true]];
    }

    #[DataProvider('bannerMembers')]
    public function testBannerPrintsTheFirstNameAsTextAndEscapesTheImageUrl(object $member): void
    {
        $GLOBALS['__phpunit_template_directory_uri'] = 'https://wordpress.example.test/theme">' . self::SCRIPT;
        $firstName = 'Ada' . self::SCRIPT;

        $xpath = $this->partial('aif-banner', ['firstName' => $firstName, 'member' => $member]);
        $image = $xpath->query('//img')->item(0);

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertInstanceOf(DOMElement::class, $image);
        self::assertStringStartsWith('https://wordpress.example.test/theme', $image->getAttribute('src'));
        self::assertSame($firstName . ', renforcez votre soutien !', trim($xpath->query('//h2')->item(0)->textContent));
    }

    public function testCheckEmailPrintsTheErrorMessageAsText(): void
    {
        $GLOBALS['__phpunit_turnstile_message'] = 'La vérification a échoué.' . self::SCRIPT;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['email' => 'donor@example.test'];

        $xpath = $this->dom(static fn () => require AIF_DONOR_SPACE_PATH . 'templates/check-email.php');

        self::assertSame(0, $xpath->query('//script')->length);
        self::assertSame($GLOBALS['__phpunit_turnstile_message'], trim($xpath->query('//div[@class="aif-error-message"]')->item(0)->textContent));
    }

    public function testMissingPartialNameCannotCloseTheHtmlComment(): void
    {
        $xpath = $this->dom(static fn () => aif_donor_space_get_partial('inconnu -->' . self::SCRIPT . '<!--'));

        self::assertSame(0, $xpath->query('//script')->length);
    }
}
