<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        return $value;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user(): WP_User
    {
        return get_userdata(get_current_user_id()) ?: new WP_User([], 0, '');
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/users/smd-role.php';

final class SmdRoleTest extends TestCase
{
    private const SMD_ID = 10;
    private const SUBSCRIBER_ID = 20;
    private const EDITOR_ID = 30;
    private const SMD_PEER_ID = 40;
    private const MULTI_ROLE_ID = 50;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_users'] = [
            self::SMD_ID => new WP_User(['smd'], self::SMD_ID),
            self::SUBSCRIBER_ID => new WP_User(['subscriber'], self::SUBSCRIBER_ID),
            self::EDITOR_ID => new WP_User(['editor'], self::EDITOR_ID),
            self::SMD_PEER_ID => new WP_User(['smd'], self::SMD_PEER_ID),
            self::MULTI_ROLE_ID => new WP_User(['subscriber', 'editor'], self::MULTI_ROLE_ID),
        ];
        $GLOBALS['__phpunit_current_user_id'] = self::SMD_ID;
        $GLOBALS['__phpunit_current_user'] = $GLOBALS['__phpunit_users'][self::SMD_ID];
    }

    public function test_grants_account_capabilities_to_smd_users(): void
    {
        $caps = amnesty_smd_grant_user_capabilities(['read' => true], [], [], $GLOBALS['__phpunit_users'][self::SMD_ID]);

        foreach (['list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users'] as $capability) {
            $this->assertTrue($caps[$capability]);
        }
    }

    public function test_does_not_grant_capabilities_to_other_roles(): void
    {
        $caps = amnesty_smd_grant_user_capabilities(['read' => true], [], [], $GLOBALS['__phpunit_users'][self::EDITOR_ID]);

        $this->assertSame(['read' => true], $caps);
    }

    #[DataProvider('managed_capabilities')]
    public function test_allows_managing_subscribers(string $cap): void
    {
        $this->assertSame(
            [$cap . '_cap'],
            amnesty_smd_restrict_meta_caps([$cap . '_cap'], $cap, self::SMD_ID, [self::SUBSCRIBER_ID])
        );
    }

    #[DataProvider('managed_capabilities')]
    public function test_denies_managing_other_accounts(string $cap): void
    {
        foreach ([self::EDITOR_ID, self::SMD_PEER_ID, self::MULTI_ROLE_ID, 999] as $target) {
            $this->assertSame(
                ['do_not_allow'],
                amnesty_smd_restrict_meta_caps(['x'], $cap, self::SMD_ID, [$target]),
                "{$cap} on user {$target}"
            );
        }
    }

    public function test_smd_can_edit_but_not_delete_or_promote_itself(): void
    {
        $this->assertSame(['x'], amnesty_smd_restrict_meta_caps(['x'], 'edit_user', self::SMD_ID, [self::SMD_ID]));
        $this->assertSame(['do_not_allow'], amnesty_smd_restrict_meta_caps(['x'], 'delete_user', self::SMD_ID, [self::SMD_ID]));
        $this->assertSame(['do_not_allow'], amnesty_smd_restrict_meta_caps(['x'], 'promote_user', self::SMD_ID, [self::SMD_ID]));
    }

    public function test_leaves_other_actors_and_capabilities_untouched(): void
    {
        $this->assertSame(['x'], amnesty_smd_restrict_meta_caps(['x'], 'edit_user', self::EDITOR_ID, [self::SMD_PEER_ID]));
        $this->assertSame(['x'], amnesty_smd_restrict_meta_caps(['x'], 'edit_post', self::SMD_ID, [123]));
    }

    public function test_smd_can_only_assign_subscriber_role(): void
    {
        $roles = ['administrator' => [], 'editor' => [], 'subscriber' => [], 'smd' => []];

        $this->assertSame(['subscriber' => []], amnesty_smd_editable_roles($roles));
    }

    public function test_editable_roles_unchanged_for_other_users(): void
    {
        $roles = ['administrator' => [], 'subscriber' => []];
        $GLOBALS['__phpunit_current_user_id'] = self::EDITOR_ID;
        $GLOBALS['__phpunit_current_user'] = $GLOBALS['__phpunit_users'][self::EDITOR_ID];

        $this->assertSame($roles, amnesty_smd_editable_roles($roles));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function managed_capabilities(): array
    {
        return [
            'edit' => ['edit_user'],
            'delete' => ['delete_user'],
            'remove' => ['remove_user'],
            'promote' => ['promote_user'],
        ];
    }
}
