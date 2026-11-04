<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Thrown by the wp_redirect stub so the pattern's `exit` is never reached.
 */
final class NewsletterRedirectCalled extends RuntimeException
{
}

/**
 * Public newsletter forms (page and footer) only collect an opt-in for the
 * typed address. They must never cancel a global unsubscribe
 * (Optout_toute_communication__c) nor delete a Salesforce lead on behalf of
 * whoever typed that address.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class NewsletterConsentTest extends TestCase
{
    private const PATTERNS = '/wp-content/themes/humanity-theme/patterns/';

    protected function setUp(): void
    {
        $GLOBALS['__nl_calls'] = [];
        $GLOBALS['__nl_local_user'] = false;
        $GLOBALS['__nl_sf_user'] = ['totalSize' => 0];
        $GLOBALS['__nl_lead'] = ['totalSize' => 1, 'records' => [['Id' => '00QLEAD']]];

        $this->defineRecordingStubs();
        require_once __DIR__ . '/stubs.php';

        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }

    private function defineRecordingStubs(): void
    {
        $record = static function (string $name, array $args): void {
            $GLOBALS['__nl_calls'][] = [$name, $args];
        };
        $GLOBALS['__nl_record'] = $record;

        if (!function_exists('get_local_user')) {
            function get_local_user(string $email): mixed
            {
                return $GLOBALS['__nl_local_user'];
            }
        }
        if (!function_exists('get_salesforce_user_with_email')) {
            function get_salesforce_user_with_email(string $email): mixed
            {
                return $GLOBALS['__nl_sf_user'];
            }
        }
        if (!function_exists('get_salesforce_nl_lead')) {
            function get_salesforce_nl_lead(string $email): mixed
            {
                $GLOBALS['__nl_record']('get_salesforce_nl_lead', [$email]);

                return $GLOBALS['__nl_lead'];
            }
        }
        if (!function_exists('delete_salesforce_data')) {
            function delete_salesforce_data(mixed ...$args): mixed
            {
                $GLOBALS['__nl_record']('delete_salesforce_data', $args);

                return null;
            }
        }
        if (!function_exists('update_salesforce_users')) {
            function update_salesforce_users(mixed ...$args): mixed
            {
                $GLOBALS['__nl_record']('update_salesforce_users', $args);

                return null;
            }
        }
        if (!function_exists('post_salesforce_users')) {
            function post_salesforce_users(mixed ...$args): mixed
            {
                $GLOBALS['__nl_record']('post_salesforce_users', $args);

                return null;
            }
        }
        if (!function_exists('insert_user')) {
            function insert_user(mixed ...$args): mixed
            {
                $GLOBALS['__nl_record']('insert_user', $args);

                return null;
            }
        }
        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(): ?string
            {
                return null;
            }
        }
        if (!function_exists('sanitize_email')) {
            function sanitize_email(string $email): string
            {
                return trim($email);
            }
        }
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field(string $text): string
            {
                return trim($text);
            }
        }
        if (!function_exists('add_query_arg')) {
            function add_query_arg(array $args, string $url): string
            {
                return $url . '?' . http_build_query($args);
            }
        }
        if (!function_exists('home_url')) {
            function home_url(string $path = ''): string
            {
                return 'https://example.test' . $path;
            }
        }
        if (!function_exists('wp_redirect')) {
            function wp_redirect(string $url): bool
            {
                throw new NewsletterRedirectCalled($url);
            }
        }
        if (!function_exists('amnesty_get_nav_menu_items')) {
            function amnesty_get_nav_menu_items(string $location): array
            {
                return [];
            }
        }
    }

    private function localUser(): object
    {
        return (object) [
            'email' => 'jane@example.test',
            'civility' => 'Mme',
            'postal_code' => '75011',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'country' => 'France',
        ];
    }

    private function salesforceContact(): array
    {
        return [
            'totalSize' => 1,
            'records' => [[
                'attributes' => ['type' => 'Contact'],
                'Id' => '003X',
                'Email' => 'jane@example.test',
                'FirstName' => 'Jane',
                'LastName' => 'Doe',
                'Pays__c' => 'France',
                'Code_Postal__c' => '75011',
            ]],
        ];
    }

    private function submit(string $pattern): string
    {
        ob_start();

        try {
            require dirname(__DIR__, 2) . self::PATTERNS . $pattern . '.php';
        } catch (NewsletterRedirectCalled $redirect) {
            return $redirect->getMessage();
        } finally {
            ob_end_clean();
        }

        $this->fail('Pattern was expected to redirect after submission.');
    }

    /**
     * @return list<array{0: string, 1: array}>
     */
    private function callsTo(string $name): array
    {
        return array_values(array_map(
            static fn (array $call): array => $call[1],
            array_filter($GLOBALS['__nl_calls'], static fn (array $call): bool => $call[0] === $name)
        ));
    }

    public function testNewsletterPageNeverSendsOptoutFlagWhenUpdatingExistingContact(): void
    {
        $GLOBALS['__nl_local_user'] = $this->localUser();
        $GLOBALS['__nl_sf_user'] = $this->salesforceContact();
        $_POST = ['sign_newsletter' => '1', 'newsletter' => 'jane@example.test'];

        $this->submit('page-nl-content');

        $updates = $this->callsTo('update_salesforce_users');
        $this->assertCount(1, $updates);
        $this->assertSame('003X', $updates[0][0]);
        $this->assertTrue($updates[0][1]['Optin_Actionaute_Newsletter_mensuelle__c']);
        $this->assertArrayNotHasKey('Optout_toute_communication__c', $updates[0][1]);
    }

    public function testFooterNeverSendsOptoutFlagWhenUpdatingExistingContact(): void
    {
        $GLOBALS['__nl_local_user'] = $this->localUser();
        $GLOBALS['__nl_sf_user'] = $this->salesforceContact();
        $_POST = ['newsletter-lead' => 'jane@example.test'];

        $this->submit('footer-content');

        $updates = $this->callsTo('update_salesforce_users');
        $this->assertCount(1, $updates);
        $this->assertSame('003X', $updates[0][0]);
        $this->assertTrue($updates[0][1]['Optin_Actionaute_Newsletter_mensuelle__c']);
        $this->assertArrayNotHasKey('Optout_toute_communication__c', $updates[0][1]);
    }

    public function testFooterNeverSendsOptoutFlagForSalesforceContactWithoutLocalUser(): void
    {
        $GLOBALS['__nl_local_user'] = false;
        $GLOBALS['__nl_sf_user'] = $this->salesforceContact();
        $_POST = ['newsletter-lead' => 'jane@example.test'];

        $this->submit('footer-content');

        $updates = $this->callsTo('update_salesforce_users');
        $this->assertCount(1, $updates);
        $this->assertSame('003X', $updates[0][0]);
        $this->assertTrue($updates[0][1]['Optin_Actionaute_Newsletter_mensuelle__c']);
        $this->assertArrayNotHasKey('Optout_toute_communication__c', $updates[0][1]);
        $this->assertArrayNotHasKey('Id', $updates[0][1]);
    }

    /**
     * @return array<string, array{0: bool, 1: bool}>
     */
    public static function newsletterPageScenarios(): array
    {
        return [
            'new visitor (no local user, no Salesforce contact)' => [false, false],
            'existing contact (local user and Salesforce contact)' => [true, true],
        ];
    }

    #[DataProvider('newsletterPageScenarios')]
    public function testNewsletterPageDoesNotTouchSalesforceLeads(bool $hasLocalUser, bool $hasContact): void
    {
        $GLOBALS['__nl_local_user'] = $hasLocalUser ? $this->localUser() : false;
        $GLOBALS['__nl_sf_user'] = $hasContact ? $this->salesforceContact() : ['totalSize' => 0];
        $_POST = [
            'sign_newsletter' => '1',
            'newsletter' => 'jane@example.test',
            'civility' => 'Mme',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'zipcode' => '75011',
            'country' => 'France',
        ];

        $redirect = $this->submit('page-nl-content');

        $this->assertStringContainsString('inscription__nl=success', $redirect);
        $this->assertSame([], $this->callsTo('get_salesforce_nl_lead'));
        $this->assertSame([], $this->callsTo('delete_salesforce_data'));
    }
}
