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
     * postmeta ACF would otherwise store under the same key. The E2E seed
     * writes these values in seed-wordpress.php. AIF_E2E_ACF_SELECT_FIELDS lists
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

add_action('plugins_loaded', function () {
    $donor_partial_helper = WP_PLUGIN_DIR . '/aif-donor-space/includes/utils.php';

    if (function_exists('aif_include_partial') || is_file($donor_partial_helper)) {
        return;
    }

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
});

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

function aif_e2e_jetpack_effects_option_name(): string
{
    return 'aif_e2e_jetpack_effects_' . aif_e2e_get_test_id();
}

function aif_e2e_get_jetpack_effects(): array
{
    $effects = get_option(aif_e2e_jetpack_effects_option_name(), []);

    return [
        'feedback_count' => (int) ($effects['feedback_count'] ?? 0),
        'mail_count' => (int) ($effects['mail_count'] ?? 0),
    ];
}

function aif_e2e_upsert_page(string $slug, string $title, int $parent_id = 0, string $content = ''): int
{
    $path = $parent_id ? get_page_uri($parent_id) . '/' . $slug : $slug;
    $page = get_page_by_path($path, OBJECT, 'page');

    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }

    $page_data = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => $parent_id,
    ];

    $page_id = wp_insert_post($page_data, true);

    return is_wp_error($page_id) ? 0 : (int) $page_id;
}

function aif_e2e_upsert_clh_petition(): ?WP_Post
{
    $petition = get_page_by_path('aif-e2e-clh-petition', OBJECT, 'petition');

    if (!$petition instanceof WP_Post) {
        global $wpdb;

        $now = current_time('mysql');
        $now_gmt = current_time('mysql', true);
        $inserted = $wpdb->insert($wpdb->posts, [
            'post_author' => 1,
            'post_date' => $now,
            'post_date_gmt' => $now_gmt,
            'post_content' => '',
            'post_title' => 'Pétition CLH (e2e)',
            'post_excerpt' => 'Pétition dédiée au scénario de skip CLH.',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => 'aif-e2e-clh-petition',
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now,
            'post_modified_gmt' => $now_gmt,
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => home_url('/petitions/aif-e2e-clh-petition/'),
            'menu_order' => 0,
            'post_type' => 'petition',
            'post_mime_type' => '',
            'comment_count' => 0,
        ]);

        if (false === $inserted) {
            return null;
        }

        clean_post_cache((int) $wpdb->insert_id);
        $petition = get_post((int) $wpdb->insert_id);
    }

    if (!$petition instanceof WP_Post) {
        return null;
    }

    update_post_meta($petition->ID, 'type', 'petition');
    update_post_meta($petition->ID, 'date_de_fin', gmdate('Y-m-d', strtotime('+1 year')));
    update_post_meta($petition->ID, 'objectif_signatures', 1000);
    update_post_meta($petition->ID, 'clh_petition', '1');

    return $petition;
}

function aif_e2e_setup_business_form_fixtures(): array
{
    global $wpdb;

    $lock_name = 'aif_e2e_business_form_fixtures';
    $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 10)', $lock_name));

    try {
        $chronicle_page_id = aif_e2e_upsert_page(
            'aif-e2e-discover-chronicle',
            'Découvrir la Chronique (e2e)',
            0,
            '<!-- wp:pattern {"slug":"amnesty/page-discover-chronicle-content"} /-->'
        );
        $donor_page_id = aif_e2e_upsert_page('aif-e2e-check-email', 'Contrôle e-mail donateur (e2e)');

        $about_page_id = aif_e2e_upsert_page('nous-connaitre', 'Nous connaître');
        $campaigns_page_id = aif_e2e_upsert_page('nos-combats', 'Nos combats', $about_page_id);
        $clh_page_id = aif_e2e_upsert_page('changez-leur-histoire', 'Changez leur histoire', $campaigns_page_id);
        $tunnel_page_id = aif_e2e_upsert_page(
            'campagne',
            'Campagne Changez leur histoire (e2e)',
            $clh_page_id,
            '<!-- wp:pattern {"slug":"amnesty/page-tunnel-clh-content"} /-->'
        );

        update_post_meta($clh_page_id, 'highlight_clh', '1');
        update_post_meta($clh_page_id, 'start_date_highligth_clh', gmdate('Y-m-d H:i:s', strtotime('-1 day')));
        update_post_meta($clh_page_id, 'end_date_highlight_clh', gmdate('Y-m-d H:i:s', strtotime('+1 year')));

        $standard_petition = get_page_by_path('aif-e2e-petition', OBJECT, 'petition');
        if ($standard_petition instanceof WP_Post) {
            delete_post_meta($standard_petition->ID, 'clh_petition');
        }
        $petition = aif_e2e_upsert_clh_petition();

        return [
            'chronicle_path' => '/aif-e2e-discover-chronicle/',
            'donor_path' => '/aif-e2e-check-email/',
            'tunnel_path' => '/nous-connaitre/nos-combats/changez-leur-histoire/campagne/',
            'petition_id' => $petition instanceof WP_Post ? (int) $petition->ID : 0,
            'ready' => $chronicle_page_id > 0 && $donor_page_id > 0 && $tunnel_page_id > 0 && $petition instanceof WP_Post,
        ];
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
    }
}

add_filter('template_include', function ($template) {
    if (!is_page('aif-e2e-check-email')) {
        return $template;
    }

    $donor_template = WP_PLUGIN_DIR . '/aif-donor-space/templates/check-email.php';

    return is_file($donor_template) ? $donor_template : $template;
}, PHP_INT_MAX);

add_action('wp_insert_post', function ($post_id, $post, $update) {
    if ($update || !$post instanceof WP_Post || 'feedback' !== $post->post_type) {
        return;
    }

    $effects = aif_e2e_get_jetpack_effects();
    ++$effects['feedback_count'];
    update_option(aif_e2e_jetpack_effects_option_name(), $effects, false);
}, 10, 3);

add_filter('pre_wp_mail', function ($return, $atts) {
    $effects = aif_e2e_get_jetpack_effects();
    ++$effects['mail_count'];
    update_option(aif_e2e_jetpack_effects_option_name(), $effects, false);

    return $return;
}, 10, 2);

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

    register_rest_route('aif-e2e/v1', '/jetpack-effects', [
        'methods' => 'GET',
        'callback' => function () {
            return new WP_REST_Response(aif_e2e_get_jetpack_effects(), 200);
        },
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('aif-e2e/v1', '/jetpack-effects', [
        'methods' => 'DELETE',
        'callback' => function () {
            delete_option(aif_e2e_jetpack_effects_option_name());
            return new WP_REST_Response(['cleared' => true], 200);
        },
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('aif-e2e/v1', '/business-form-fixtures', [
        'methods' => 'POST',
        'callback' => function () {
            return new WP_REST_Response(aif_e2e_setup_business_form_fixtures(), 200);
        },
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('aif-e2e/v1', '/clh-state', [
        'methods' => 'GET',
        'callback' => function () {
            $skipped_petitions = function_exists('amnesty_get_clh_skipped_petitions')
                ? amnesty_get_clh_skipped_petitions()
                : [];

            return new WP_REST_Response([
                'skipped_petitions' => array_values(array_map('intval', $skipped_petitions)),
            ], 200);
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
