<?php

/**
 * Widest srcset candidate we ever want to advertise.
 *
 * Matches both the widest size the theme registers (`hero-lg`, 2560px) and the
 * `big_image_size_threshold` ceiling applied at upload, so nothing we
 * deliberately generate is discarded — only the oversized candidates Photon
 * derives from the untouched original.
 */
const AMNESTY_IMAGE_CDN_MAX_SRCSET_WIDTH = 2560;

/**
 * Quality that forces the CDN off its lossless WebP path.
 *
 * WordPress.com switches from lossless (VP8L) to lossy (VP8) encoding somewhere
 * between 80 and 79, so 79 is the highest value that escapes the trap below
 * while giving up as little fidelity as possible.
 */
const AMNESTY_IMAGE_CDN_LOSSY_QUALITY = 79;

/**
 * Upload paths the CDN encodes as lossless WebP, at a ruinous cost.
 *
 * At its default quality the CDN picks lossless VP8L for a handful of our
 * uploads, which is catastrophic on photographic content: the homepage hero
 * measured 442KB against 41KB for the exact same 1024x512 crop encoded lossy.
 * Requesting quality 79 flips those images to VP8 and reclaims ~90%.
 *
 * This list is deliberately narrow. WordPress.com applies adaptive per-image
 * quality by default — often well below 79 — so forcing a fixed quality site
 * wide *inflates* the images it had already compressed harder (measured up to
 * +124% on one press photo). Only add an entry once you have measured that the
 * default response is VP8L for it.
 *
 * To check a candidate, compare bytes and encoding between the two responses:
 *   curl -sH 'Accept: image/webp' '<cdn-url>' | head -c 16 | xxd
 * 'VP8L' at offset 12 means lossless; 'VP8 ' means lossy.
 *
 * Paths are matched as substrings of the full image URL.
 *
 * @return string[]
 */
function amnesty_image_cdn_lossless_paths(): array
{
    return apply_filters('amnesty_image_cdn_lossless_paths', []);
}

/**
 * Upload paths that must skip the CDN entirely and stay on the origin.
 *
 * Escape hatch for images the CDN mangles in a way quality cannot fix. Empty by
 * default: prefer an entry in `amnesty_image_cdn_lossless_paths()`, which keeps
 * the edge delivery and the on-the-fly resizing.
 *
 * @return string[]
 */
function amnesty_image_cdn_excluded_paths(): array
{
    return apply_filters('amnesty_image_cdn_excluded_paths', []);
}

/**
 * Test an image URL against one of the path lists above.
 *
 * @param string[] $paths
 */
function amnesty_image_cdn_url_matches(string $image_url, array $paths): bool
{
    foreach ($paths as $path) {
        if ($path !== '' && str_contains($image_url, $path)) {
            return true;
        }
    }

    return false;
}

/**
 * Force lossy encoding on the uploads the CDN would otherwise send losslessly.
 */
add_filter('jetpack_photon_pre_args', function ($args, string $image_url) {
    if (!is_array($args) || !amnesty_image_cdn_url_matches($image_url, amnesty_image_cdn_lossless_paths())) {
        return $args;
    }

    $args['quality'] = AMNESTY_IMAGE_CDN_LOSSY_QUALITY;

    return $args;
}, 20, 2);

/**
 * Keep the listed uploads off the Jetpack image CDN.
 */
add_filter('jetpack_photon_skip_for_url', function (bool $skip, string $image_url): bool {
    if ($skip) {
        return true;
    }

    return amnesty_image_cdn_url_matches($image_url, amnesty_image_cdn_excluded_paths());
}, 10, 2);

/**
 * Drop srcset candidates wider than the widest slot we render.
 *
 * Core already caps candidates via `max_srcset_image_width` (2048 by default),
 * but Photon hooks `wp_calculate_image_srcset` at priority 10 and appends
 * full-size candidates afterwards — on a 4000px original that means the browser
 * is offered 3000w and 4000w variants no layout ever needs. Running at 20 puts
 * this after Photon so those additions are pruned too.
 *
 * At least one candidate is always kept: stripping every source would leave an
 * empty srcset and lose the resolution switching entirely.
 */
add_filter('wp_calculate_image_srcset', function (array $sources): array {
    $kept = array_filter(
        $sources,
        fn ($width): bool => (int) $width <= AMNESTY_IMAGE_CDN_MAX_SRCSET_WIDTH,
        ARRAY_FILTER_USE_KEY
    );

    if (!$kept && $sources) {
        $widths = array_map('intval', array_keys($sources));

        return [min($widths) => $sources[min($widths)]];
    }

    return $kept;
}, 20, 1);
