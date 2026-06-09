<?php
/**
 * Plugin Name: Amnesty Dev Turnstile
 * Description: Provides Cloudflare Turnstile dummy keys for the local wp-env stack.
 * Version: 1.0.0
 */

if (function_exists('wp_get_environment_type') && !in_array(wp_get_environment_type(), ['local', 'development'], true)) {
    return;
}

$turnstile_site_key = getenv('TURNSTILE_SITE_KEY') ?: '1x00000000000000000000BB';
$turnstile_secret_key = getenv('TURNSTILE_SECRET_KEY') ?: '1x0000000000000000000000000000000AA';

putenv('TURNSTILE_SITE_KEY=' . $turnstile_site_key);
putenv('TURNSTILE_SECRET_KEY=' . $turnstile_secret_key);

$_ENV['TURNSTILE_SITE_KEY'] = $turnstile_site_key;
$_ENV['TURNSTILE_SECRET_KEY'] = $turnstile_secret_key;
$_SERVER['TURNSTILE_SITE_KEY'] = $turnstile_site_key;
$_SERVER['TURNSTILE_SECRET_KEY'] = $turnstile_secret_key;
