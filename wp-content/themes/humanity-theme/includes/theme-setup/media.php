<?php

declare(strict_types=1);

if (! function_exists('amnesty_image_quality')) {
    /**
     * Stop WP from ruining JPGs
     *
     * @package Amnesty\ThemeSetup
     *
     * @return int
     */
    function amnesty_image_quality()
    {
        return 100;
    }
}

if (! function_exists('amnesty_add_jfif_support')) {
    /**
     * Add support for JFIF images
     *
     * @package Amnesty\ThemeSetup
     *
     * @param array<string,string> $mimes existing list of mime types
     *
     * @return array<string,string>
     */
    function amnesty_add_jfif_support(array $mimes): array
    {
        $mimes['jfif'] = 'image/jpeg';
        return $mimes;
    }
}

if (! defined('AMNESTY_BIG_IMAGE_SIZE_THRESHOLD')) {
    define('AMNESTY_BIG_IMAGE_SIZE_THRESHOLD', 3200);
}

if (! function_exists('amnesty_big_image_size_threshold')) {
    /**
     * Limit original uploaded images while preserving enough pixels for hero crops.
     *
     * The largest registered crop is hero-lg at 2560x710. A 3200px threshold keeps
     * a 3200x900 source intact, which leaves enough height for that wide crop.
     *
     * @package Amnesty\ThemeSetup
     *
     * @return int
     */
    function amnesty_big_image_size_threshold(): int
    {
        return AMNESTY_BIG_IMAGE_SIZE_THRESHOLD;
    }
}

if (! defined('AMNESTY_PHOTON_DELIVERY_QUALITY')) {
    define('AMNESTY_PHOTON_DELIVERY_QUALITY', 80);
}

if (! function_exists('amnesty_photon_delivery_args')) {
    /**
     * Pin the quality Photon encodes JPEG sources with.
     *
     * Photon mirrors the quality of the source file, and `amnesty_image_quality`
     * above deliberately keeps originals at 100. At that value libwebp switches
     * to lossless, so Photon was serving WebP several times heavier than the
     * JPEG it replaced. Asking for an explicit quality keeps it on the lossy
     * encoder without touching the archived originals.
     *
     * Only JPEG sources are pinned. Photon already serves PNG sources as
     * lossless WebP, which is what logos and line art need: forcing them
     * through the lossy encoder adds visible ringing around text and often
     * produces a larger file than the lossless one.
     *
     * @package Amnesty\ThemeSetup
     *
     * @param array<string,mixed>|string $args      existing Photon arguments
     * @param string                     $image_url url of the image being served
     *
     * @return array<string,mixed>|string
     */
    function amnesty_photon_delivery_args(array|string $args, string $image_url = ''): array|string
    {
        if (! is_array($args)) {
            return $args;
        }

        $path = (string) wp_parse_url($image_url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, [ 'jpg', 'jpeg', 'jfif' ], true)) {
            return $args;
        }

        $args['quality'] = $args['quality'] ?? AMNESTY_PHOTON_DELIVERY_QUALITY;

        return $args;
    }
}

if (! function_exists('amnesty_remove_gutenberg_media_options')) {
    /**
     * Remove entries from the "Media" tab in the Gutenberg inserter
     *
     * @package Amnesty\ThemeSetup
     *
     * @param array<string,mixed> $settings the block editor settings
     *
     * @return array<string,mixed>
     */
    function amnesty_remove_gutenberg_media_options(array $settings): array
    {
        $settings['enableOpenverseMediaCategory'] = false;
        return $settings;
    }
}

add_filter('jpeg_quality', 'amnesty_image_quality');
add_filter('wp_editor_set_quality', 'amnesty_image_quality');
add_filter('big_image_size_threshold', 'amnesty_big_image_size_threshold');

remove_filter('the_content', 'prepend_attachment');

add_action('after_setup_theme', 'amnesty_theme_image_sizes');
add_filter('image_size_names_choose', 'amnesty_custom_image_sizes');

add_filter('jetpack_photon_pre_args', 'amnesty_photon_delivery_args', 10, 2);

add_filter('mime_types', 'amnesty_add_jfif_support');

add_filter('block_editor_settings_all', 'amnesty_remove_gutenberg_media_options');
