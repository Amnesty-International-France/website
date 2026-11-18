<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/newsletter/links.php';

final class NewsletterLinksTest extends TestCase
{
    private const POST_ID = 777;
    private const PERMALINK = 'https://www.amnesty.fr/petitions/genocide-gaza/';
    private const UTMS = [
        'utm_source' => 'email_newsletter',
        'utm_medium' => 'email',
        'utm_campaign' => 'newshebdo_29102026',
    ];
    private const UTM_QUERY = '?utm_source=email_newsletter&utm_medium=email&utm_campaign=newshebdo_29102026';

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [self::POST_ID => self::PERMALINK];
    }

    #[DataProvider('linkCases')]
    public function testBuildsInternalLinkUrl(string $postType, ?string $codeOrigineNl, string $expected): void
    {
        if ($codeOrigineNl !== null) {
            $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['reserved_codeorigine'] = $codeOrigineNl;
        }

        $post = (object) ['ID' => self::POST_ID, 'post_type' => $postType];

        self::assertSame($expected, amnesty_newsletter_internal_link_url($post, self::UTMS));
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function linkCases(): iterable
    {
        yield 'petition with a code origine NL' => [
            'petition',
            '26B11EN794',
            self::PERMALINK . self::UTM_QUERY . '&reserved_originecode=26B11EN794',
        ];
        yield 'petition without a code origine NL' => [
            'petition',
            null,
            self::PERMALINK . self::UTM_QUERY,
        ];
        yield 'petition with an empty code origine NL' => [
            'petition',
            '',
            self::PERMALINK . self::UTM_QUERY,
        ];
        yield 'code origine NL is URL-encoded' => [
            'petition',
            '26B&11 EN#794',
            self::PERMALINK . self::UTM_QUERY . '&reserved_originecode=26B%2611%20EN%23794',
        ];
        yield 'non-petition post ignores the code origine NL' => [
            'post',
            '26B11EN794',
            self::PERMALINK . self::UTM_QUERY,
        ];
    }
}
