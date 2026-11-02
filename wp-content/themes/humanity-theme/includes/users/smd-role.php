<?php

declare(strict_types=1);

/**
 * Account management for the "SMD" role (Service Membres et Donateurs).
 *
 * SMD users can create, edit and delete WordPress accounts, but only accounts
 * whose sole role is "subscriber". They cannot hand out any other role, and
 * cannot touch administrators, editors, or other SMD accounts.
 *
 * @package Amnesty\Users
 */

if (! function_exists('amnesty_smd_role')) {
    /**
     * Slug of the SMD role.
     */
    function amnesty_smd_role(): string
    {
        return (string) apply_filters('amnesty_smd_role', 'smd');
    }
}

if (! function_exists('amnesty_smd_user_capabilities')) {
    /**
     * Capabilities granted to SMD users to manage accounts.
     *
     * @return string[]
     */
    function amnesty_smd_user_capabilities(): array
    {
        return ['list_users', 'create_users', 'edit_users', 'delete_users', 'promote_users', 'remove_users'];
    }
}

if (! function_exists('amnesty_smd_can_manage_roles')) {
    /**
     * Whether an SMD user may manage an account holding the given roles.
     *
     * @param string[] $roles roles of the targeted account
     */
    function amnesty_smd_can_manage_roles(array $roles): bool
    {
        return $roles === ['subscriber'];
    }
}

if (! function_exists('amnesty_smd_grant_user_capabilities')) {
    /**
     * Grants the account management capabilities to SMD users.
     *
     * @param array<string, bool> $allcaps all capabilities of the user
     * @param array               $caps    primitive capabilities being checked
     * @param array               $args    [0] requested capability, [1] user ID
     * @param WP_User             $user    the user being checked
     *
     * @return array<string, bool>
     */
    function amnesty_smd_grant_user_capabilities($allcaps, $caps, $args, $user)
    {
        if (! in_array(amnesty_smd_role(), (array) $user->roles, true)) {
            return $allcaps;
        }

        foreach (amnesty_smd_user_capabilities() as $capability) {
            $allcaps[$capability] = true;
        }

        return $allcaps;
    }
}

add_filter('user_has_cap', 'amnesty_smd_grant_user_capabilities', 10, 4);

if (! function_exists('amnesty_smd_restrict_meta_caps')) {
    /**
     * Denies SMD users any action on accounts that are not plain subscribers.
     *
     * @param string[] $caps    primitive capabilities required
     * @param string   $cap     requested capability
     * @param int      $user_id ID of the user performing the action
     * @param array    $args    [0] is the targeted user ID
     *
     * @return string[]
     */
    function amnesty_smd_restrict_meta_caps($caps, $cap, $user_id, $args)
    {
        if (! in_array($cap, ['edit_user', 'delete_user', 'remove_user', 'promote_user'], true)) {
            return $caps;
        }

        $actor = get_userdata((int) $user_id);

        if (! $actor || ! in_array(amnesty_smd_role(), (array) $actor->roles, true)) {
            return $caps;
        }

        $target = isset($args[0]) ? get_userdata((int) $args[0]) : false;

        // Editing one's own profile stays possible; deleting or re-roling it does not.
        if ($target && (int) $target->ID === (int) $user_id) {
            return $cap === 'edit_user' ? $caps : ['do_not_allow'];
        }

        if (! $target || ! amnesty_smd_can_manage_roles(array_values((array) $target->roles))) {
            return ['do_not_allow'];
        }

        return $caps;
    }
}

add_filter('map_meta_cap', 'amnesty_smd_restrict_meta_caps', 10, 4);

if (! function_exists('amnesty_smd_editable_roles')) {
    /**
     * Only lets SMD users assign the subscriber role.
     *
     * @param array<string, array> $roles roles the current user may assign
     *
     * @return array<string, array>
     */
    function amnesty_smd_editable_roles($roles)
    {
        $user = wp_get_current_user();

        if (! $user || ! in_array(amnesty_smd_role(), (array) $user->roles, true)) {
            return $roles;
        }

        return array_intersect_key($roles, ['subscriber' => true]);
    }
}

add_filter('editable_roles', 'amnesty_smd_editable_roles');
