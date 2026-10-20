<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (!class_exists('WP_REST_Users_Controller')) {
    $GLOBALS['__phpunit_users_controller_core_checks'] = [];
    $GLOBALS['__phpunit_users_controller_core_result'] = true;

    /**
     * Stand-in for core's WP_REST_Users_Controller. Its permission checks
     * record the call and return $GLOBALS['__phpunit_users_controller_core_result'],
     * so tests can tell whether the subclass delegated to core and passed its
     * result through. Signatures stay untyped, like core's, so the subclass
     * overrides remain compatible.
     */
    class WP_REST_Users_Controller
    {
        protected string $namespace = 'wp/v2';
        protected string $rest_base = 'users';

        public function __construct()
        {
        }

        public function get_items_permissions_check($request)
        {
            $GLOBALS['__phpunit_users_controller_core_checks'][] = 'get_items_permissions_check';

            return $GLOBALS['__phpunit_users_controller_core_result'];
        }

        public function get_item_permissions_check($request)
        {
            $GLOBALS['__phpunit_users_controller_core_checks'][] = 'get_item_permissions_check';

            return $GLOBALS['__phpunit_users_controller_core_result'];
        }
    }
}

if (!function_exists('rest_authorization_required_code')) {
    function rest_authorization_required_code(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }
}

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('is_multisite')) {
    $GLOBALS['__phpunit_is_multisite'] = false;

    function is_multisite(): bool
    {
        return $GLOBALS['__phpunit_is_multisite'];
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability, mixed ...$args): bool
    {
        return ($GLOBALS['__phpunit_user_capabilities'][$capability] ?? null) === $args;
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/users/class-users-controller.php';

final class UsersControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__phpunit_users_controller_core_checks'] = [];
        $GLOBALS['__phpunit_users_controller_core_result'] = true;
        $GLOBALS['__phpunit_user_capabilities'] = [];
        $GLOBALS['__phpunit_current_user_id'] = 0;
        $GLOBALS['__phpunit_is_multisite'] = false;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function permissionChecks(): iterable
    {
        yield 'collection' => ['get_items_permissions_check'];
        yield 'single user' => ['get_item_permissions_check'];
    }

    #[DataProvider('permissionChecks')]
    public function testRejectsAnAnonymousCallerWith401(string $check): void
    {
        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('rest_forbidden', $result->get_error_code());
        self::assertSame(['status' => 401], $result->get_error_data());
        self::assertSame([], $GLOBALS['__phpunit_users_controller_core_checks']);
    }

    #[DataProvider('permissionChecks')]
    public function testRejectsALoggedInUserWithoutListUsersWith403(string $check): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 42;

        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('rest_forbidden', $result->get_error_code());
        self::assertSame(['status' => 403], $result->get_error_data());
        self::assertSame([], $GLOBALS['__phpunit_users_controller_core_checks']);
    }

    #[DataProvider('permissionChecks')]
    public function testDelegatesToCoreWhenTheUserCanListUsers(string $check): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 1;
        $GLOBALS['__phpunit_user_capabilities'] = ['list_users' => []];

        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertTrue($result);
        self::assertSame([$check], $GLOBALS['__phpunit_users_controller_core_checks']);
    }

    #[DataProvider('permissionChecks')]
    public function testReturnsCoreErrorWhenTheUserCanListUsers(string $check): void
    {
        $GLOBALS['__phpunit_current_user_id'] = 1;
        $GLOBALS['__phpunit_user_capabilities'] = ['list_users' => []];
        $coreError = new WP_Error('rest_user_invalid_id', 'Invalid user ID.', ['status' => 404]);
        $GLOBALS['__phpunit_users_controller_core_result'] = $coreError;

        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertSame($coreError, $result);
        self::assertSame([$check], $GLOBALS['__phpunit_users_controller_core_checks']);
    }

    #[DataProvider('permissionChecks')]
    public function testRejectsASiteAdminWithoutManageNetworkUsersOnMultisite(string $check): void
    {
        $GLOBALS['__phpunit_is_multisite'] = true;
        $GLOBALS['__phpunit_current_user_id'] = 7;
        $GLOBALS['__phpunit_user_capabilities'] = ['list_users' => []];

        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertInstanceOf(WP_Error::class, $result);
        self::assertSame('rest_forbidden', $result->get_error_code());
        self::assertSame(['status' => 403], $result->get_error_data());
        self::assertSame([], $GLOBALS['__phpunit_users_controller_core_checks']);
    }

    #[DataProvider('permissionChecks')]
    public function testDelegatesToCoreWhenTheUserCanManageNetworkUsersOnMultisite(string $check): void
    {
        $GLOBALS['__phpunit_is_multisite'] = true;
        $GLOBALS['__phpunit_current_user_id'] = 1;
        $GLOBALS['__phpunit_user_capabilities'] = ['manage_network_users' => []];

        $result = (new Amnesty\REST_API\Users_Controller())->{$check}(new WP_REST_Request());

        self::assertTrue($result);
        self::assertSame([$check], $GLOBALS['__phpunit_users_controller_core_checks']);
    }
}
