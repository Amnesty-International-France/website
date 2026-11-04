<?php

declare(strict_types=1);

/**
 * Setup shared by the tests of the redirects of a rejected signature, which tell a
 * tunnel submission from a petition-page one. The stand-ins are declared when the test
 * runs, not when this file loads: other Petitions tests declare their own is_page() and
 * verify_turnstile().
 */
trait ClhTunnelRedirectSetup
{
    private const PETITION_ID = 101;
    private const TUNNEL_URL = 'https://wordpress.example.test/campagne/tunnel/';
    private const PETITION_URL = 'https://wordpress.example.test/petitions/une-petition/';

    /**
     * Loads the signature handler, then seeds an active campaign with one petition, the
     * tunnel page and an active session. Stand-ins specific to a test go before this call.
     */
    private function setUpTunnelRedirect(): void
    {
        require_once __DIR__ . '/clh-tunnel-stand-ins.php';

        if (!function_exists('wp_get_referer')) {
            // The tunnel form posts back to its own URL, so WordPress finds no usable referer.
            function wp_get_referer(): string|false
            {
                return false;
            }
        }

        if (!function_exists('is_page')) {
            function is_page(): bool
            {
                return $GLOBALS['post'] instanceof WP_Post;
            }
        }

        if (!function_exists('get_query_var')) {
            function get_query_var(string $query_var): mixed
            {
                return '';
            }
        }

        if (!function_exists('get_page_template_slug')) {
            function get_page_template_slug(int $post_id): string
            {
                return '';
            }
        }

        if (!function_exists('amnesty_get_clh_petition_tunnel_page')) {
            function amnesty_get_clh_petition_tunnel_page(): ?WP_Post
            {
                return $GLOBALS['__phpunit_clh_tunnel_page'];
            }
        }

        if (!function_exists('verify_turnstile')) {
            function verify_turnstile(): ?string
            {
                return null;
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/post-types/petitions.php';

        $campaign = new WP_Post();
        $campaign->ID = 10;
        $petition = new WP_Post();
        $petition->ID = self::PETITION_ID;
        $GLOBALS['__phpunit_clh_campaign'] = $campaign;
        $GLOBALS['__phpunit_clh_petitions'] = [$petition];
        $GLOBALS['__phpunit_post_permalinks'][self::PETITION_ID] = self::PETITION_URL;
        $tunnel_page = new WP_Post();
        $tunnel_page->ID = 20;
        $GLOBALS['__phpunit_clh_tunnel_page'] = $tunnel_page;
        $GLOBALS['__phpunit_post_permalinks'][$tunnel_page->ID] = self::TUNNEL_URL;
        // Current page of the request; null outside any page.
        $GLOBALS['post'] = null;
        // An active session makes amnesty_start_secure_session() a no-op.
        ini_set('session.use_cookies', '0');
        ini_set('session.save_path', sys_get_temp_dir());
        session_start();
        $_SESSION = [];
    }

    private function redirectLocation(): string
    {
        try {
            amnesty_handle_petition_signature();
        } catch (ClhRedirect $redirect) {
            return $redirect->getMessage();
        }

        self::fail('The signature handler did not redirect.');
    }
}
