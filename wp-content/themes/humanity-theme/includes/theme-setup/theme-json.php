<?php

declare(strict_types=1);

if (!function_exists('merge_theme_json')) {
    /**
     * Merge theme.json from branding with humanity
     */
    function merge_theme_json($theme_json)
    {
        $humanity_theme_json = json_decode(file_get_contents(get_stylesheet_directory() . '/theme.json'), true);
        $theme_json->update_with($humanity_theme_json);
        return $theme_json;
    }
}

add_filter('wp_theme_json_data_theme', 'merge_theme_json', 100);
