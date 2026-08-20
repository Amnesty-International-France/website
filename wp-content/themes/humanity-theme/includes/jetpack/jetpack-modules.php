<?php

add_filter('jetpack_get_available_modules', function ($modules) {
    unset($modules['videopress']);

    /**
     * Yoast generates the XML sitemaps (see includes/seo/sitemap.php). Jetpack's
     * own module only knows about 'post' and 'page' and has no notion of custom
     * post types, archives or the noindex flags set in Yoast, so leaving it on
     * publishes a second, contradictory sitemap at /sitemap.xml.
     *
     * Removing the module here rather than toggling it in the admin keeps it off
     * across deploys and Jetpack auto-updates: Modules::get_active() intersects
     * the stored 'active_modules' option with the available ones.
     */
    unset($modules['sitemaps']);

    return $modules;
}, 10, 1);
