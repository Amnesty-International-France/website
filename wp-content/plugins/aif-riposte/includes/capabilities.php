<?php

/**
 * Riposte victory capabilities.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Riposte capabilities, keyed by the core capability granting the same right.
 *
 * Every role keeps on Ripostes the rights it has on posts.
 *
 * @return array<string,string>
 */
function aif_riposte_get_capability_map(): array
{
    return [
        'edit_posts'             => 'edit_ripostes',
        'edit_others_posts'      => 'edit_others_ripostes',
        'edit_private_posts'     => 'edit_private_ripostes',
        'edit_published_posts'   => 'edit_published_ripostes',
        'publish_posts'          => 'publish_ripostes',
        'read_private_posts'     => 'read_private_ripostes',
        'delete_posts'           => 'delete_ripostes',
        'delete_others_posts'    => 'delete_others_ripostes',
        'delete_private_posts'   => 'delete_private_ripostes',
        'delete_published_posts' => 'delete_published_ripostes',
        'manage_options'         => 'manage_riposte_settings',
    ];
}

/**
 * Roles with every Riposte capability, whatever their rights on posts.
 *
 * @return array<int,string>
 */
function aif_riposte_get_manager_roles(): array
{
    return [
        'administrator',
        'editor',
        'redac'
    ];
}

/**
 * Grant the Riposte capabilities when they are checked.
 *
 * Manager roles also get manage_categories, required by the "Pays" menu:
 * it edits the theme location taxonomy, shared with the other content types.
 *
 * @param array<string,bool> $allcaps Capabilities of the user.
 * @param array<int,string>  $caps    Primitive capabilities being checked.
 * @param array<int,mixed>   $args    Requested capability, user ID and object ID.
 * @param WP_User            $user    User being checked.
 *
 * @return array<string,bool>
 */
function aif_riposte_grant_capabilities(array $allcaps, array $caps, array $args, WP_User $user): array
{
    unset($caps, $args);

    $is_manager = [] !== array_intersect($user->roles, aif_riposte_get_manager_roles());

    foreach (aif_riposte_get_capability_map() as $core_cap => $riposte_cap) {
        if ($is_manager || ! empty($allcaps[ $core_cap ])) {
            $allcaps[ $riposte_cap ] = true;
        }
    }

    if ($is_manager) {
        $allcaps['manage_categories'] = true;
    }

    return $allcaps;
}
add_filter('user_has_cap', 'aif_riposte_grant_capabilities', 10, 4);
