<?php

add_filter('jetpack_sitemap_image_skip_post', '__return_true');
add_filter('jetpack_sitemap_video_skip_post', '__return_true');

/**
 * Safety net on individual sitemap entries, mirroring includes/seo/sitemap.php
 * for Yoast: drop password-protected posts and any URL under /mon-espace/
 * (auth-gated personal space), since Jetpack builds its sitemap from a raw
 * SQL query on post_status='publish' with no awareness of the login gate.
 */
add_filter('jetpack_sitemap_skip_post', function (bool $skip, object $post): bool {
    if ($skip || !isset($post->ID)) {
        return $skip;
    }

    if (!empty($post->post_password)) {
        return true;
    }

    $path = wp_parse_url(get_permalink($post->ID), PHP_URL_PATH);
    if ($path && str_starts_with($path, '/mon-espace/')) {
        return true;
    }

    return false;
}, 10, 2);
