<?php

declare(strict_types=1);

/**
 * Restrictions for the User Switching plugin.
 *
 * Only administrators may switch to another account, and never to an account
 * whose only role is "subscriber" (members and donors). Without this, any
 * role able to edit users (such as SMD on subscribers) could switch accounts.
 *
 * @package Amnesty\Users
 */

if (! function_exists('amnesty_user_switching_can_switch')) {
    /**
     * Whether a user holding the given roles may switch accounts.
     *
     * @param string[] $roles roles of the user performing the switch
     */
    function amnesty_user_switching_can_switch(array $roles): bool
    {
        return in_array('administrator', $roles, true);
    }
}

if (! function_exists('amnesty_user_switching_is_allowed_target')) {
    /**
     * Whether an account holding the given roles may be switched to.
     *
     * @param string[] $roles roles of the targeted account
     */
    function amnesty_user_switching_is_allowed_target(array $roles): bool
    {
        return array_diff($roles, ['subscriber']) !== [];
    }
}

if (! function_exists('amnesty_user_switching_restrict_meta_caps')) {
    /**
     * Denies account switching to non administrators and towards subscribers.
     *
     * @param string[] $caps    primitive capabilities required
     * @param string   $cap     requested capability
     * @param int      $user_id ID of the user performing the action
     * @param array    $args    [0] is the targeted user ID for switch_to_user
     *
     * @return string[]
     */
    function amnesty_user_switching_restrict_meta_caps($caps, $cap, $user_id, $args)
    {
        if (! in_array($cap, ['switch_to_user', 'switch_off'], true)) {
            return $caps;
        }

        $actor = get_userdata((int) $user_id);

        if (! $actor || ! amnesty_user_switching_can_switch(array_values((array) $actor->roles))) {
            return ['do_not_allow'];
        }

        if ($cap === 'switch_off') {
            return $caps;
        }

        $target = isset($args[0]) ? get_userdata((int) $args[0]) : false;

        if (! $target || ! amnesty_user_switching_is_allowed_target(array_values((array) $target->roles))) {
            return ['do_not_allow'];
        }

        return $caps;
    }
}

add_filter('map_meta_cap', 'amnesty_user_switching_restrict_meta_caps', 10, 4);
