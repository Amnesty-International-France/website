<?php

declare(strict_types=1);

if (! function_exists('add_menu_separator')) {
    /**
     * Add an admin menu separator
     *
     * Does not allow overriding positions that are already in use
     *
     * @package Amnesty\Admin\Options
     *
     * @param int $position the position at which to insert the separator
     *
     * @return void
     */
    function add_menu_separator(int $position): void
    {
        global $menu;

        if (! isset($menu[ $position ])) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            $menu[ $position ] = [ '', 'read', 'separator' . $position, '', 'wp-menu-separator' ];
            return;
        }

        add_menu_separator($position + 1);
    }
}

if (! function_exists('amnesty_reorganise_admin_menu')) {
    /**
     * Reorganise the admin menu
     */
    function amnesty_reorganise_admin_menu(): void
    {
        if (is_network_admin()) {
            return;
        }

        global $menu;

        // look items up by slug, their position depends on what is registered
        $media = null;
        foreach ($menu as $key => $item) {
            $slug = $item[2] ?? '';

            if ('upload.php' === $slug) {
                $media = $item;
            }

            // remove comments, original media item
            if ('upload.php' === $slug || 'edit-comments.php' === $slug) {
                unset($menu[ $key ]);
            }
        }

        // move media menu item to the first free slot after the custom post types
        if (null !== $media) {
            add_menu_separator(30);

            $position = 31;
            while (isset($menu[ $position ])) {
                ++$position;
            }

            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            $menu[ $position ] = $media;
            add_menu_separator($position + 1);
        }

        add_menu_separator(40);
    }
}

// reorganise the admin menu a bit
add_action('admin_menu', 'amnesty_reorganise_admin_menu', 1000);
