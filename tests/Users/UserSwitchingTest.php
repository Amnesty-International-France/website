<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/users/user-switching.php';

final class UserSwitchingTest extends TestCase
{
    private const ADMIN_ID = 10;
    private const OTHER_ADMIN_ID = 11;
    private const EDITOR_ID = 20;
    private const AUTHOR_ID = 21;
    private const SUBSCRIBER_ID = 30;
    private const MULTI_ROLE_ID = 31;
    private const NO_ROLE_ID = 32;
    private const SMD_ID = 40;

    protected function setUp(): void
    {
        $GLOBALS['__phpunit_users'] = [
            self::ADMIN_ID => new WP_User(['administrator'], self::ADMIN_ID),
            self::OTHER_ADMIN_ID => new WP_User(['administrator'], self::OTHER_ADMIN_ID),
            self::EDITOR_ID => new WP_User(['editor'], self::EDITOR_ID),
            self::AUTHOR_ID => new WP_User(['author'], self::AUTHOR_ID),
            self::SUBSCRIBER_ID => new WP_User(['subscriber'], self::SUBSCRIBER_ID),
            self::MULTI_ROLE_ID => new WP_User(['subscriber', 'editor'], self::MULTI_ROLE_ID),
            self::NO_ROLE_ID => new WP_User([], self::NO_ROLE_ID),
            self::SMD_ID => new WP_User(['smd'], self::SMD_ID),
        ];
    }

    public function test_admin_can_switch_to_back_office_accounts(): void
    {
        foreach ([self::EDITOR_ID, self::AUTHOR_ID, self::MULTI_ROLE_ID, self::OTHER_ADMIN_ID] as $target) {
            $this->assertSame(
                ['switch_to_user'],
                amnesty_user_switching_restrict_meta_caps(['switch_to_user'], 'switch_to_user', self::ADMIN_ID, [$target]),
                "switch to user {$target}"
            );
        }
    }

    public function test_admin_cannot_switch_to_subscribers_or_unknown_accounts(): void
    {
        foreach ([self::SUBSCRIBER_ID, self::NO_ROLE_ID, 999] as $target) {
            $this->assertSame(
                ['do_not_allow'],
                amnesty_user_switching_restrict_meta_caps(['switch_to_user'], 'switch_to_user', self::ADMIN_ID, [$target]),
                "switch to user {$target}"
            );
        }
    }

    public function test_keeps_the_plugin_refusal_to_switch_to_oneself(): void
    {
        $this->assertSame(
            ['switch_to_user', 'do_not_allow'],
            amnesty_user_switching_restrict_meta_caps(['switch_to_user', 'do_not_allow'], 'switch_to_user', self::ADMIN_ID, [self::ADMIN_ID])
        );
    }

    public function test_admin_cannot_switch_without_target(): void
    {
        $this->assertSame(
            ['do_not_allow'],
            amnesty_user_switching_restrict_meta_caps(['switch_to_user'], 'switch_to_user', self::ADMIN_ID, [])
        );
    }

    public function test_non_admins_cannot_switch(): void
    {
        foreach ([self::SMD_ID, self::EDITOR_ID, 999] as $actor) {
            foreach ([self::SUBSCRIBER_ID, self::AUTHOR_ID] as $target) {
                $this->assertSame(
                    ['do_not_allow'],
                    amnesty_user_switching_restrict_meta_caps(['switch_to_user'], 'switch_to_user', $actor, [$target]),
                    "user {$actor} switching to user {$target}"
                );
            }
        }
    }

    public function test_only_admins_can_switch_off(): void
    {
        $this->assertSame(
            ['switch_off'],
            amnesty_user_switching_restrict_meta_caps(['switch_off'], 'switch_off', self::ADMIN_ID, [])
        );
        $this->assertSame(
            ['do_not_allow'],
            amnesty_user_switching_restrict_meta_caps(['switch_off'], 'switch_off', self::SMD_ID, [])
        );
    }

    public function test_leaves_other_capabilities_untouched(): void
    {
        $this->assertSame(
            ['edit_users'],
            amnesty_user_switching_restrict_meta_caps(['edit_users'], 'edit_user', self::SMD_ID, [self::SUBSCRIBER_ID])
        );
    }
}
