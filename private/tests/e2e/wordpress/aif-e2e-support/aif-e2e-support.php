<?php
/**
 * Plugin Name: Amnesty E2E Support
 * Description: Minimal local-only stubs required by Playwright wp-env tests.
 * Version: 1.0.0
 */

if (function_exists('wp_get_environment_type') && !in_array(wp_get_environment_type(), ['local', 'development'], true)) {
    return;
}

if (!defined('AIF_E2E_ACF_SELECT_FIELDS')) {
    define('AIF_E2E_ACF_SELECT_FIELDS', ['type']);
}

if (!function_exists('get_field')) {
    /**
     * Real ACF isn't installed here, so this falls back to reading the raw
     * postmeta ACF would otherwise store under the same key - seed values via
     * `wp post meta set` in seed-wordpress.sh. AIF_E2E_ACF_SELECT_FIELDS lists
     * the "select" fields business code reads as ['value' => ..., 'label' =>
     * ...] (e.g. `get_field('type')['value']`); everything else returns the
     * raw string.
     */
    function get_field($selector = null, $post_id = false, $format_value = true)
    {
        $resolved_post_id = $post_id ?: get_the_ID();

        if (!$selector || !$resolved_post_id) {
            return null;
        }

        $value = get_post_meta($resolved_post_id, $selector, true);

        if ($value === '') {
            return null;
        }

        if (in_array($selector, AIF_E2E_ACF_SELECT_FIELDS, true)) {
            return ['value' => $value, 'label' => $value];
        }

        return $value;
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

/**
 * Shared shape for every faked pre_http_request response below (Turnstile and
 * Salesforce): WordPress expects this exact array whenever a filter preempts
 * the real HTTP request, only the JSON-encoded body actually differs per mock.
 */
function aif_e2e_fake_http_response(array $body): array
{
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

    return aif_e2e_fake_http_response($body);
}, 10, 3);

const AIF_E2E_SALESFORCE_BASE_URL = 'https://fake-salesforce.e2e.test/';

if (!getenv('AIF_SALESFORCE_URL')) {
    putenv('AIF_SALESFORCE_URL=' . AIF_E2E_SALESFORCE_BASE_URL);
}

/**
 * Namespaces the call log per test (see support/fixtures.mjs' salesforceTestId)
 * instead of one shared option: Playwright can run tests concurrently against
 * this same wp-env backend, and a shared log would let them overwrite each
 * other's calls. The X-AIF-E2E-Test-Id header is on every request a test
 * makes, so it's readable here via $_SERVER while handling that same request.
 */
function aif_e2e_get_test_id(): string
{
    $test_id = isset($_SERVER['HTTP_X_AIF_E2E_TEST_ID']) ? (string) $_SERVER['HTTP_X_AIF_E2E_TEST_ID'] : '';
    $test_id = sanitize_key($test_id);

    return '' !== $test_id ? $test_id : 'default';
}

function aif_e2e_salesforce_calls_option_name(): string
{
    return 'aif_e2e_salesforce_calls_' . aif_e2e_get_test_id();
}

/**
 * Mocks every outbound Salesforce call (includes/salesforce/data.php +
 * authentification.php always go through AIF_SALESFORCE_URL) and records
 * each one in an option, so specs for journeys that end with a real,
 * synchronous Salesforce call (e.g. newsletter signup) can assert it was
 * actually triggered via the /aif-e2e/v1/salesforce-calls REST route below,
 * without a real network call ever leaving this environment.
 */
add_filter('pre_http_request', function ($preempt, $args, $url) {
    if (!str_starts_with($url, AIF_E2E_SALESFORCE_BASE_URL)) {
        return $preempt;
    }

    $option_name = aif_e2e_salesforce_calls_option_name();
    $calls = get_option($option_name, []);
    $calls[] = [
        'method' => $args['method'] ?? 'GET',
        'url' => $url,
        'body' => $args['body'] ?? null,
    ];
    update_option($option_name, $calls, false);

    $path = substr($url, strlen(AIF_E2E_SALESFORCE_BASE_URL));

    if (str_starts_with($path, 'services/oauth2/token')) {
        // issued_at is genuinely in milliseconds (see the "warning" comment in
        // refresh_salesforce_token()) - a real-looking value keeps the token
        // valid for this request and any others in the same test.
        $body = [
            'access_token' => 'fake-e2e-access-token',
            'issued_at' => (string) floor(microtime(true) * 1000),
            'instance_url' => rtrim(AIF_E2E_SALESFORCE_BASE_URL, '/'),
            'token_type' => 'Bearer',
        ];
    } elseif (str_contains($path, 'query/?q=')) {
        // SOQL lookup (existing Contact/Lead by email). Defaults to "not
        // found" so the create branch of whichever handler is under test
        // runs; a test can seed a match via aif_e2e_sf_query_found=1.
        $found = isset($_REQUEST['aif_e2e_sf_query_found']) && '1' === $_REQUEST['aif_e2e_sf_query_found'];
        $body = $found
            ? ['totalSize' => 1, 'records' => [['Id' => 'fake-sf-id-existing']]]
            : ['totalSize' => 0, 'records' => []];
    } else {
        // Any create/update/delete call (Contact, Lead, Case, ...).
        $body = ['success' => true, 'id' => 'fake-sf-id-new'];
    }

    return aif_e2e_fake_http_response($body);
}, 10, 3);

add_action('rest_api_init', function () {
    register_rest_route('aif-e2e/v1', '/salesforce-calls', [
        'methods' => 'GET',
        'callback' => function () {
            return new WP_REST_Response(get_option(aif_e2e_salesforce_calls_option_name(), []), 200);
        },
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('aif-e2e/v1', '/salesforce-calls', [
        'methods' => 'DELETE',
        'callback' => function () {
            delete_option(aif_e2e_salesforce_calls_option_name());
            return new WP_REST_Response(['cleared' => true], 200);
        },
        'permission_callback' => '__return_true',
    ]);
});

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
