<?php

/**
 * Widest srcset candidate we ever want to advertise.
 *
 * Derived from `big_image_size_threshold`, the ceiling the upload pipeline
 * applies to every original (`includes/theme-setup/media.php`), so the cap
 * tracks that value instead of drifting from it: everything the theme
 * deliberately generates survives the pruning, and only the oversized
 * candidates Photon derives from pre-threshold originals are dropped.
 *
 * Resolved at runtime rather than as a constant because the threshold is
 * defined in another include and load order between the two is not guaranteed.
 * 2560 is the WordPress core default, used if the theme constant is absent.
 */
function amnesty_image_cdn_max_srcset_width(): int
{
    $max = defined('AMNESTY_BIG_IMAGE_SIZE_THRESHOLD')
        ? (int) AMNESTY_BIG_IMAGE_SIZE_THRESHOLD
        : 2560;

    return (int) apply_filters('amnesty_image_cdn_max_srcset_width', $max);
}

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
add_filter('jetpack_photon_pre_args', function ($args, $image_url) {
    if (!amnesty_image_cdn_url_matches((string) $image_url, amnesty_image_cdn_lossless_paths())) {
        return $args;
    }

    // The hook documents `array|string $args` and `cdn_url()` accepts both, so
    // skipping the string form would silently leave those call sites lossless.
    if (is_string($args)) {
        wp_parse_str($args, $parsed);
        $args = $parsed;
    }

    if (!is_array($args)) {
        return $args;
    }

    $args['quality'] = AMNESTY_IMAGE_CDN_LOSSY_QUALITY;

    return $args;
}, 20, 2);

/**
 * Keep the listed uploads off the Jetpack image CDN.
 */
add_filter('jetpack_photon_skip_for_url', function ($skip, $image_url) {
    // Jetpack skips the CDN on any value that is not strictly false, so an
    // earlier filter's decision has to pass through verbatim — coercing it to a
    // boolean would re-enable the CDN for an image someone excluded on purpose.
    if (false !== $skip) {
        return $skip;
    }

    return amnesty_image_cdn_url_matches((string) $image_url, amnesty_image_cdn_excluded_paths());
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
 * Pruning is abandoned rather than applied partially when it would leave fewer
 * than two candidates: core drops the whole srcset below that count
 * (`wp-includes/media.php:1534`), which would push the oversized `src` onto
 * every visitor — the exact regression this filter exists to prevent.
 *
 * `$sources` is left untyped because core tolerates a non-array here and
 * `add_filter('wp_calculate_image_srcset', '__return_false')` is the documented
 * way to switch responsive images off; a typed parameter would turn that into a
 * fatal TypeError.
 */
add_filter('wp_calculate_image_srcset', function ($sources) {
    if (!is_array($sources)) {
        return $sources;
    }

    $max = amnesty_image_cdn_max_srcset_width();

    $kept = array_filter(
        $sources,
        fn ($width): bool => (int) $width <= $max,
        ARRAY_FILTER_USE_KEY
    );

    return count($kept) < 2 ? $sources : $kept;
}, 20, 1);
