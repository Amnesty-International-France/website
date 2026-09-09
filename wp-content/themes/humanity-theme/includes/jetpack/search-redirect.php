<?php

declare(strict_types=1);

/**
 * Keep Jetpack searches on /?s=<term> instead of redirecting to /search/<term>/.
 *
 * The theme's Search_Filters::prettify_search() 302-redirects every request
 * carrying an `s` query var to the pretty /search/<term>/ URL. AIF has no
 * /search page and does not want one: Jetpack Instant Search runs on the
 * homepage and reopens its overlay from the `s` query param.
 *
 * That redirect broke Back-navigation after a search — pressing Back restored a
 * /?s=<term> history entry, which the server then 302-redirected to
 * /search/<term>/ (MAINT-143). Returning false to this filter short-circuits the
 * redirect (and the Yoast JSON-LD search URL rewrite in permalink.php), so
 * searches stay on /?s=<term> and Back returns to the homepage with the overlay.
 *
 * @see \Amnesty\Search_Filters::prettify_search()
 * @package Amnesty\Jetpack
 */
add_filter('amnesty_prettify_search_url', '__return_false');
