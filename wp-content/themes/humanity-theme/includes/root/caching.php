<?php

declare(strict_types=1);


if (! function_exists('amnesty_is_cacheable_request')) {
    /**
     * Whether the current response is public and identical for every visitor.
     *
     * Deny by default: a blocklist always ends up forgetting a page, so the
     * question asked here is "can this response be shared?", not "is this page
     * known to be private?".
     *
     * @package Amnesty\Caching
     *
     * @return bool
     */
    function amnesty_is_cacheable_request(): bool
    {
        // never cache a personalised response.
        if (is_user_logged_in()) {
            return false;
        }

        if (is_preview() || is_search() || is_404() || is_feed()) {
            return false;
        }

        // donor space: every page-*.php template of the account area.
        $private_pages = [
            'mes-informations-personnelles',
            'modification-coordonnees-bancaire',
            'mes-dons',
            'mes-recus-fiscaux',
            'mes-demandes',
            'connectez-vous',
            'creer-votre-compte',
            'verifier-votre-email',
            'modifier-mon-mot-de-passe',
            'mot-de-passe-oublie',
            'se-deconnecter',
        ];

        if (is_page($private_pages)) {
            return false;
        }

        // The CLH tunnel renders a per-session, randomized petition (array_rand on the
        // not-yet-signed/skipped list). Caching it makes "Passer la pétition" redirect
        // back to a stale page that always shows the first petition. Never cache it.
        if (
            function_exists('amnesty_is_clh_petition_tunnel_page')
            && amnesty_is_clh_petition_tunnel_page()
        ) {
            return false;
        }

        // WooCommerce: cart, checkout and account pages depend on the session.
        if (
            function_exists('is_cart')
            && function_exists('is_checkout')
            && function_exists('is_account_page')
            && (is_cart() || is_checkout() || is_account_page())
        ) {
            return false;
        }

        return true;
    }
}

if (! function_exists('amnesty_cache_header')) {
    /**
     * Add caching headers for the browser and the CDN.
     *
     * @package Amnesty\Caching
     *
     * @return void
     */
    function amnesty_cache_header(): void
    {
        // sanity check.
        if (headers_sent()) {
            return;
        }

        // disable in development.
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return;
        }

        // explicit per-environment opt-in (see .env).
        if (getenv('AIF_HTTP_CACHE') !== '1') {
            return;
        }

        // disable for previews.
        // phpcs:ignore
        if (isset($_GET['preview']) || is_preview()) {
            return;
        }

        // disable for WP core
        // phpcs:ignore
        if (preg_match('/^\/wp-*/', $_SERVER['REQUEST_URI'])) {
            return;
        }

        if (! amnesty_is_cacheable_request()) {
            header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            return;
        }

        // Short browser TTL (browser caches cannot be purged), long edge TTL
        // (Cloudflare is purged on publish and on deploy).
        header(
            sprintf(
                'Cache-Control: public, max-age=%d, s-maxage=%d, stale-while-revalidate=%d, must-revalidate',
                MINUTE_IN_SECONDS * 5,
                HOUR_IN_SECONDS * 6,
                MINUTE_IN_SECONDS
            )
        );
    }
}

add_action('template_redirect', 'amnesty_cache_header');

if (! function_exists('amnesty_cloudflare_edge_cache_header')) {
    /**
     * Keep the Cloudflare APO edge-cache header in sync with our own rules.
     *
     * The Cloudflare plugin sends `cf-edge-cache: cache,platform=wordpress` on
     * `init` for every anonymous request. That is too early for is_page() or
     * is_cart(), so it cannot know about the CLH tunnel or the cart. Re-send
     * the header once the main query has run; header() replaces by default.
     *
     * Purging itself is left to the plugin, which already purges post-related
     * URLs on `transition_post_status`.
     *
     * @package Amnesty\Caching
     *
     * @return void
     */
    function amnesty_cloudflare_edge_cache_header(): void
    {
        if (! defined('CLOUDFLARE_PLUGIN_DIR') || headers_sent()) {
            return;
        }

        if (amnesty_is_cacheable_request()) {
            return;
        }

        header('cf-edge-cache: no-cache');
    }
}

add_action('template_redirect', 'amnesty_cloudflare_edge_cache_header');
