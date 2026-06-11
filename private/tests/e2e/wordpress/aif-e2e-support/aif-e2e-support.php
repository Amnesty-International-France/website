<?php
/**
 * Plugin Name: Amnesty E2E Support
 * Description: Minimal local-only stubs required by Playwright wp-env tests.
 * Version: 1.0.0
 */

if (function_exists('wp_get_environment_type') && !in_array(wp_get_environment_type(), ['local', 'development'], true)) {
    return;
}

if (!function_exists('get_field')) {
    function get_field($selector = null, $post_id = false, $format_value = true)
    {
        return null;
    }
}

if (!function_exists('get_fields')) {
    function get_fields($post_id = false, $format_value = true)
    {
        return [];
    }
}

if (!function_exists('the_field')) {
    function the_field($selector = null, $post_id = false, $format_value = true): void
    {
        $value = get_field($selector, $post_id, $format_value);

        if (is_scalar($value)) {
            echo esc_html((string) $value);
        }
    }
}

if (!function_exists('aif_include_partial')) {
    function aif_include_partial(string $name, array $args = []): void
    {
        if ('alert' !== $name) {
            return;
        }

        $title = isset($args['title']) ? (string) $args['title'] : '';
        $content = isset($args['content']) ? (string) $args['content'] : '';

        printf(
            '<div role="alert" class="form-mess %s"><strong>%s</strong><p>%s</p></div>',
            esc_attr((string) ($args['state'] ?? 'info')),
            esc_html($title),
            wp_kses_post($content)
        );
    }
}

add_filter('pre_http_request', function ($preempt, $args, $url) {
    if ('https://challenges.cloudflare.com/turnstile/v0/siteverify' !== $url) {
        return $preempt;
    }

    if (!isset($_REQUEST['aif_e2e_turnstile_verify_success'])) {
        return $preempt;
    }

    $success = '1' === sanitize_text_field(wp_unslash($_REQUEST['aif_e2e_turnstile_verify_success']));
    $error = isset($_REQUEST['aif_e2e_turnstile_verify_error'])
        ? sanitize_text_field(wp_unslash($_REQUEST['aif_e2e_turnstile_verify_error']))
        : 'invalid-input-response';
    $body = ['success' => $success];

    if (!$success) {
        $body['error-codes'] = [$error];
    }

    return [
        'headers' => [],
        'body' => wp_json_encode($body),
        'response' => [
            'code' => 200,
            'message' => 'OK',
        ],
        'cookies' => [],
        'filename' => null,
    ];
}, 10, 3);

$site_key = $_REQUEST['aif_e2e_turnstile_site_key'] ?? null;
$secret_key = $_REQUEST['aif_e2e_turnstile_secret_key'] ?? null;

$site_key = null !== $site_key
    ? sanitize_text_field(wp_unslash($site_key))
    : '1x00000000000000000000BB';

$secret_key = null !== $secret_key
    ? sanitize_text_field(wp_unslash($secret_key))
    : '1x0000000000000000000000000000000AA';

putenv('TURNSTILE_SITE_KEY=' . $site_key);
putenv('TURNSTILE_SECRET_KEY=' . $secret_key);

$_ENV['TURNSTILE_SITE_KEY'] = $site_key;
$_ENV['TURNSTILE_SECRET_KEY'] = $secret_key;
$_SERVER['TURNSTILE_SITE_KEY'] = $site_key;
$_SERVER['TURNSTILE_SECRET_KEY'] = $secret_key;
