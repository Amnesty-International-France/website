<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/tunnel-form-retry.php';

/**
 * The fields typed in a rejected tunnel signature are shown once, on the next tunnel
 * display, then forgotten.
 */
final class ClhTunnelFormRetryTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testRememberedFieldsArePulledOnceThenForgotten(): void
    {
        amnesty_remember_clh_signature_form([
            'user_email' => 'abc@localhost',
            'civility' => 'Mme',
            'user_firstname' => '<b>Ada</b>',
            'user_lastname' => 'Lovelace',
            'user_zipcode' => '75001',
            'user_country' => 'France',
            'sign_petition' => '1',
        ]);

        self::assertSame([
            'user_email' => 'abc@localhost',
            'civility' => 'Mme',
            'user_firstname' => 'Ada',
            'user_lastname' => 'Lovelace',
            'user_zipcode' => '75001',
            'user_country' => 'France',
            'user_phone' => '',
        ], amnesty_pull_clh_signature_form());
        self::assertSame([], amnesty_pull_clh_signature_form());
    }

    public function testSlashesAddedByWordPressToPostedValuesAreRemoved(): void
    {
        amnesty_remember_clh_signature_form(['user_lastname' => "D\\'Arc"]);

        self::assertSame("D'Arc", amnesty_pull_clh_signature_form()['user_lastname'] ?? null);
    }

    public function testLongValuesAreCutToTheSizeTheUsersTableAccepts(): void
    {
        amnesty_remember_clh_signature_form([
            'user_firstname' => str_repeat('é', 300),
            'user_zipcode' => str_repeat('7', 30),
        ]);

        $values = amnesty_pull_clh_signature_form();

        self::assertSame(str_repeat('é', 100), $values['user_firstname'] ?? null);
        self::assertSame(str_repeat('7', 20), $values['user_zipcode'] ?? null);
    }

    public function testNonTextPostedValueIsStoredEmpty(): void
    {
        amnesty_remember_clh_signature_form(['user_firstname' => ['Ada']]);

        self::assertSame('', amnesty_pull_clh_signature_form()['user_firstname'] ?? null);
    }

    #[DataProvider('unusablePayloads')]
    public function testUnusableSessionPayloadIsIgnoredAndRemoved(mixed $payload): void
    {
        $_SESSION['clh_signature_form'] = $payload;

        self::assertSame([], amnesty_pull_clh_signature_form());
        self::assertArrayNotHasKey('clh_signature_form', $_SESSION);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function unusablePayloads(): iterable
    {
        yield 'string' => ['abc@localhost'];
        yield 'nested array' => [['user_email' => ['abc@localhost']]];
        yield 'object' => [(object) ['user_email' => 'abc@localhost']];
    }

    public function testNothingRememberedMeansNoValues(): void
    {
        self::assertSame([], amnesty_pull_clh_signature_form());
    }
}
