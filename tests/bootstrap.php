<?php

declare(strict_types=1);

/**
 * Shared stubs for PHP unit tests.
 *
 * These tests do not boot WordPress (no DB, no wp-load.php): business code is
 * required directly and only the WP/plugin functions it calls are stubbed.
 * Keep stubs here limited to ones that are generic and reusable across test
 * suites (e.g. WP_CLI, get_field). Anything specific to one suite's scenario
 * (fake Salesforce responses, in-memory fixtures, ...) belongs in that
 * suite's own test file instead.
 */

if (!class_exists('WP_CLI')) {
    class WP_CLI
    {
        public static function log(string $message): void
        {
        }

        public static function error(string $message): void
        {
        }

        public static function success(string $message): void
        {
        }

        public static function add_command(string $name, callable|object $callable): void
        {
        }
    }
}

if (!function_exists('get_field')) {
    $GLOBALS['__phpunit_acf_field_values'] = [];

    /**
     * Stub for ACF's get_field(). Tests can seed a return value via
     * $GLOBALS['__phpunit_acf_field_values'][$post_id][$selector]; anything
     * not seeded resolves to an empty string.
     */
    function get_field(string $selector, int $post_id = 0): mixed
    {
        return $GLOBALS['__phpunit_acf_field_values'][$post_id][$selector] ?? '';
    }
}

if (!function_exists('update_field')) {
    /**
     * Stub for ACF's update_field(). Writes into the same in-memory store
     * get_field() (above) reads from.
     */
    function update_field(string $selector, mixed $value, int $post_id): bool
    {
        $GLOBALS['__phpunit_acf_field_values'][$post_id][$selector] = $value;

        return true;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error(mixed $thing): bool
    {
        return $thing instanceof WP_Error;
    }
}

if (!function_exists('get_post')) {
    $GLOBALS['__phpunit_posts'] = [];

    /**
     * Stub for get_post(). Tests seed a post via
     * $GLOBALS['__phpunit_posts'][$post_id] = (object) ['ID' => $post_id, ...];
     * anything not seeded resolves to null (matching WordPress when the ID
     * doesn't exist).
     */
    function get_post(int $post_id): ?object
    {
        return $GLOBALS['__phpunit_posts'][$post_id] ?? null;
    }
}

if (!function_exists('get_the_terms')) {
    $GLOBALS['__phpunit_post_terms'] = [];

    /**
     * Stub for get_the_terms(). Tests seed terms via
     * $GLOBALS['__phpunit_post_terms'][$post_id][$taxonomy] = [...]; anything
     * not seeded resolves to false (matching WordPress when there are none).
     */
    function get_the_terms(object|int $post, string $taxonomy): mixed
    {
        $post_id = is_object($post) ? $post->ID : $post;

        return $GLOBALS['__phpunit_post_terms'][$post_id][$taxonomy] ?? false;
    }
}

if (!function_exists('get_post_permalink')) {
    $GLOBALS['__phpunit_post_permalinks'] = [];

    /**
     * Stub for get_post_permalink(). Tests seed a value via
     * $GLOBALS['__phpunit_post_permalinks'][$post_id].
     */
    function get_post_permalink(int $post_id): string|false
    {
        return $GLOBALS['__phpunit_post_permalinks'][$post_id] ?? false;
    }
}

if (!function_exists('sanitize_email')) {
    /**
     * Simplified stand-in for WordPress's sanitize_email() (which strips
     * disallowed characters via a stricter local-part/domain regex) - good
     * enough for tests that pass already-clean or obviously-invalid strings.
     */
    function sanitize_email(string $email): string
    {
        return trim($email);
    }
}

if (!function_exists('is_email')) {
    function is_email(string $email): string|false
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : false;
    }
}

if (!function_exists('is_user_logged_in')) {
    $GLOBALS['__phpunit_current_user_id'] = 0;

    /**
     * Stub for is_user_logged_in()/get_current_user_id(). Tests seed the
     * "logged in" state via $GLOBALS['__phpunit_current_user_id'] (0 = logged out,
     * matching WordPress's own convention).
     */
    function is_user_logged_in(): bool
    {
        return $GLOBALS['__phpunit_current_user_id'] > 0;
    }

    function get_current_user_id(): int
    {
        return $GLOBALS['__phpunit_current_user_id'];
    }
}

if (!function_exists('update_user_meta')) {
    $GLOBALS['__phpunit_user_meta'] = [];

    /**
     * In-memory stub for the WP user-meta API. Not a faithful reimplementation
     * (e.g. no meta_id, no support for non-unique keys) - just enough for
     * business code that reads/writes a single value per user/key.
     */
    function update_user_meta(int $user_id, string $key, mixed $value): bool
    {
        $GLOBALS['__phpunit_user_meta'][$user_id][$key] = $value;

        return true;
    }

    function get_user_meta(int $user_id, string $key = '', bool $single = false): mixed
    {
        $value = $GLOBALS['__phpunit_user_meta'][$user_id][$key] ?? '';

        return $single ? $value : [$value];
    }

    function delete_user_meta(int $user_id, string $key): bool
    {
        unset($GLOBALS['__phpunit_user_meta'][$user_id][$key]);

        return true;
    }
}

if (!function_exists('wp_verify_nonce')) {
    $GLOBALS['__phpunit_valid_nonces'] = [];

    /**
     * Stub for wp_verify_nonce(). Tests seed accepted nonces via
     * $GLOBALS['__phpunit_valid_nonces']; anything else is rejected.
     */
    function wp_verify_nonce(string $nonce, int|string $action = -1): int|false
    {
        return in_array($nonce, $GLOBALS['__phpunit_valid_nonces'], true) ? 1 : false;
    }
}

if (!function_exists('add_action')) {
    $GLOBALS['__phpunit_registered_actions'] = [];

    /**
     * No-op recorder for add_action(). Only records the registration so that
     * requiring a file which calls add_action() at the top level (e.g. to
     * register a REST route on 'rest_api_init') doesn't fatal; it does NOT
     * invoke the callback, so tests call the registered function directly.
     */
    function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $GLOBALS['__phpunit_registered_actions'][$hook][] = $callback;
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        public function __construct(
            public readonly string $code = '',
            public readonly string $message = '',
            public readonly mixed $data = null,
        ) {
        }

        public function get_error_code(): string
        {
            return $this->code;
        }

        public function get_error_message(): string
        {
            return $this->message;
        }
    }
}

if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /**
         * @param array<string,mixed> $jsonParams
         * @param array<string,string> $headers
         */
        public function __construct(
            private readonly array $jsonParams = [],
            private readonly array $headers = [],
        ) {
        }

        public function get_json_params(): array
        {
            return $this->jsonParams;
        }

        public function get_header(string $name): ?string
        {
            return $this->headers[$name] ?? null;
        }

        public function get_param(string $key): mixed
        {
            return $this->jsonParams[$key] ?? null;
        }
    }
}

if (!function_exists('post_salesforce_data')) {
    $GLOBALS['__phpunit_salesforce_data_calls'] = [];
    $GLOBALS['__phpunit_salesforce_data_response'] = [];
    $GLOBALS['__phpunit_salesforce_data_response_queue'] = [];

    /**
     * Generic stub for the theme-level Salesforce REST helpers
     * (includes/salesforce/data.php), shared because several domains
     * (petitions, users, ...) call the *same* functions by name - defining
     * them again per test file would fatal with "cannot redeclare" once
     * more than one such suite runs in the same PHPUnit process.
     *
     * Tests read $GLOBALS['__phpunit_salesforce_data_calls'] to assert on
     * what was sent. For a single canned response reused by every call, seed
     * $GLOBALS['__phpunit_salesforce_data_response']. When a test triggers
     * several calls that must each return something different (e.g. a POST
     * followed by a GET), seed $GLOBALS['__phpunit_salesforce_data_response_queue']
     * instead - one entry per call, consumed in order; falls back to the
     * single response once the queue is empty.
     */
    function __phpunit_next_salesforce_data_response(): mixed
    {
        if (!empty($GLOBALS['__phpunit_salesforce_data_response_queue'])) {
            return array_shift($GLOBALS['__phpunit_salesforce_data_response_queue']);
        }

        return $GLOBALS['__phpunit_salesforce_data_response'];
    }

    function get_salesforce_data(string $url): mixed
    {
        $GLOBALS['__phpunit_salesforce_data_calls'][] = ['method' => 'GET', 'url' => $url];

        return __phpunit_next_salesforce_data_response();
    }

    function post_salesforce_data(string $url, array $params = []): mixed
    {
        $GLOBALS['__phpunit_salesforce_data_calls'][] = ['method' => 'POST', 'url' => $url, 'params' => $params];

        return __phpunit_next_salesforce_data_response();
    }

    function patch_salesforce_data(string $url, array $params = []): mixed
    {
        $GLOBALS['__phpunit_salesforce_data_calls'][] = ['method' => 'PATCH', 'url' => $url, 'params' => $params];

        return __phpunit_next_salesforce_data_response();
    }
}

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
    define('ARRAY_N', 'ARRAY_N');
    define('OBJECT', 'OBJECT');
    define('OBJECT_K', 'OBJECT_K');
}

if (!class_exists('wpdb')) {
    /**
     * Minimal in-memory stand-in for WordPress's $wpdb, covering only the
     * methods this codebase's data-access functions call. It does not run
     * any real SQL - prepare() substitutes %d/%s/%i placeholders itself, and
     * every call is recorded on ->calls so tests can assert on the exact
     * query/params sent. Seed a canned response via ->var_result/->row_result/
     * ->results_result/->insert_result/->update_result/->query_result before
     * calling the code under test.
     */
    class wpdb
    {
        public string $prefix = 'wp_';
        public int $insert_id = 0;

        /** @var array<int,array<string,mixed>> */
        public array $calls = [];

        public mixed $var_result = null;
        public mixed $row_result = null;
        public array $results_result = [];
        public bool|int $insert_result = 1;
        public bool|int $update_result = 1;
        public bool|int $query_result = 1;

        public function get_charset_collate(): string
        {
            return 'CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        }

        public function prepare(string $query, mixed ...$args): string
        {
            if (count($args) === 1 && is_array($args[0])) {
                $args = $args[0];
            }

            $i = 0;

            return preg_replace_callback('/%[dsi]/', function (array $matches) use (&$i, $args): string {
                $value = $args[$i++];

                return match ($matches[0]) {
                    '%d' => (string) (int) $value,
                    '%s' => "'" . addslashes((string) $value) . "'",
                    '%i' => '`' . str_replace('`', '', (string) $value) . '`',
                };
            }, $query);
        }

        public function get_var(string $query): mixed
        {
            $this->calls[] = ['method' => 'get_var', 'query' => $query];

            return $this->var_result;
        }

        public function get_row(string $query): mixed
        {
            $this->calls[] = ['method' => 'get_row', 'query' => $query];

            return $this->row_result;
        }

        public function get_results(string $query, string $output = OBJECT): array
        {
            $this->calls[] = ['method' => 'get_results', 'query' => $query, 'output' => $output];

            return $this->results_result;
        }

        public function insert(string $table, array $data, mixed $format = null): bool|int
        {
            $this->calls[] = ['method' => 'insert', 'table' => $table, 'data' => $data, 'format' => $format];

            return $this->insert_result;
        }

        public function update(string $table, array $data, array $where, mixed $format = null, mixed $where_format = null): bool|int
        {
            $this->calls[] = ['method' => 'update', 'table' => $table, 'data' => $data, 'where' => $where, 'format' => $format, 'where_format' => $where_format];

            return $this->update_result;
        }

        public function query(string $query): bool|int
        {
            $this->calls[] = ['method' => 'query', 'query' => $query];

            return $this->query_result;
        }
    }
}

if (!isset($GLOBALS['wpdb'])) {
    $GLOBALS['wpdb'] = new wpdb();
}

if (!function_exists('get_posts')) {
    $GLOBALS['__phpunit_get_posts_calls'] = [];
    $GLOBALS['__phpunit_get_posts_result'] = [];

    /**
     * Stub for get_posts(). Tests seed the return value via
     * $GLOBALS['__phpunit_get_posts_result'] and can inspect the query args
     * passed via $GLOBALS['__phpunit_get_posts_calls'].
     */
    function get_posts(array $args = []): array
    {
        $GLOBALS['__phpunit_get_posts_calls'][] = $args;

        return $GLOBALS['__phpunit_get_posts_result'];
    }
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        public function __construct(
            public readonly mixed $data = [],
            public readonly int $status = 200,
        ) {
        }

        public function get_data(): mixed
        {
            return $this->data;
        }

        public function get_status(): int
        {
            return $this->status;
        }
    }
}
