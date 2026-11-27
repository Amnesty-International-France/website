<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * AIF-419: the page of an EDH resource of type Document redirects to its file.
 */
final class EdhDocumentRedirectTest extends TestCase
{
    private const POST_ID = 163121;
    private const FILE_URL = 'https://wordpress.example.test/wp-content/uploads/2026/11/EDH_livret_islamophobie.pdf';

    public static function setUpBeforeClass(): void
    {
        if (!function_exists('is_admin')) {
            function is_admin(): bool
            {
                return false;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/edh.php';
    }

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID] = [
            'support_edh' => 'Document',
            'document_private' => false,
            'upload_du_document' => ['ID' => 42, 'url' => self::FILE_URL],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['__phpunit_acf_field_values'][self::POST_ID]);
    }

    public function testDocumentWithFileRedirectsToItsFile(): void
    {
        self::assertSame(self::FILE_URL, amnesty_edh_document_redirect_url(self::POST_ID));
    }

    public function testReplacedFileIsFollowed(): void
    {
        $new_url = 'https://wordpress.example.test/wp-content/uploads/2026/12/EDH_livret_v2.pdf';
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['upload_du_document'] = ['ID' => 43, 'url' => $new_url];

        self::assertSame($new_url, amnesty_edh_document_redirect_url(self::POST_ID));
    }

    public function testPageResourceIsNotRedirected(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['support_edh'] = 'Page';

        self::assertSame('', amnesty_edh_document_redirect_url(self::POST_ID));
    }

    public function testDocumentWithoutFileKeepsItsPage(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['upload_du_document'] = '';

        self::assertSame('', amnesty_edh_document_redirect_url(self::POST_ID));
    }

    public function testPrivateDocumentIsNotRedirected(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][self::POST_ID]['document_private'] = true;

        self::assertSame('', amnesty_edh_document_redirect_url(self::POST_ID));
    }
}
