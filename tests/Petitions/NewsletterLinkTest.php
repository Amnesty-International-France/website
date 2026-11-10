<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/newsletter-link.php';

final class NewsletterLinkTest extends TestCase
{
    private const POST_ID = 777;
    private const PERMALINK = 'https://www.amnesty.fr/petitions/genocide-gaza/';
    private const UTMS = [
        'utm_source' => 'email_newsletter',
        'utm_medium' => 'email',
        'utm_campaign' => 'newshebdo_29102026',
    ];

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_acf_field_values'] = [];
        $GLOBALS['__phpunit_post_permalinks'] = [self::POST_ID => self::PERMALINK];
    }

    private static function post(string $type): object
    {
        return (object) ['ID' => self::POST_ID, 'post_type' => $type];
    }

    public function testAddsCodeOrigineNlToPetitionLinks(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['reserved_codeorigine'] = '26B11EN794';

        self::assertSame(
            self::PERMALINK . '?utm_source=email_newsletter&utm_medium=email&utm_campaign=newshebdo_29102026&reserved_originecode=26B11EN794',
            newsletter_internal_link_url(self::post('petition'), self::UTMS)
        );
    }

    public function testKeepsOnlyUtmsWhenPetitionHasNoCodeOrigineNl(): void
    {
        self::assertSame(
            self::PERMALINK . '?utm_source=email_newsletter&utm_medium=email&utm_campaign=newshebdo_29102026',
            newsletter_internal_link_url(self::post('petition'), self::UTMS)
        );
    }

    public function testIgnoresCodeOrigineNlOnNonPetitionPosts(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['reserved_codeorigine'] = '26B11EN794';

        self::assertSame(
            self::PERMALINK . '?utm_source=email_newsletter&utm_medium=email&utm_campaign=newshebdo_29102026',
            newsletter_internal_link_url(self::post('post'), self::UTMS)
        );
    }
}
