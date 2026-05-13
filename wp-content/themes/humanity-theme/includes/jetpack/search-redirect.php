<?php

declare(strict_types=1);

/**
 * Fix JetPack Search back-navigation.
 *
 * JetPack Search calls history.pushState('/search/<term>') as the user types.
 * Pressing Back then reloads that URL server-side, which does not exist.
 *
 * Fix: patch History.prototype so every pushState/replaceState call converts
 * /search/<term> to /?s=<term> before it reaches the history stack.
 * The Back button then restores /?s=<term>, which JetPack detects and
 * re-opens the search overlay automatically.
 *
 * @package Amnesty\Jetpack
 */
add_action(
    'wp_head',
    function () {
        ?>
<script>
(function () {
    function rewriteSearchUrl(url) {
        const match = String(url).match(/\/search\/([^/?#]+)/);
        return match ? '/?s=' + encodeURIComponent(decodeURIComponent(match[1])) : url;
    }

    const origPush    = History.prototype.pushState;
    const origReplace = History.prototype.replaceState;

    History.prototype.pushState = function (state, title, url) {
        return Reflect.apply(origPush, this, [state, title, rewriteSearchUrl(url)]);
    };

    History.prototype.replaceState = function (state, title, url) {
        return Reflect.apply(origReplace, this, [state, title, rewriteSearchUrl(url)]);
    };
}());
</script>
        <?php
    },
    1
);
