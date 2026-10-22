<?php

declare(strict_types=1);

final class RedirectTestException extends RuntimeException
{
}

function wp_redirect(string $location, int $status = 302): never
{
    throw new RedirectTestException($location);
}

function get_header(): void
{
}

function get_footer(): void
{
}

function aif_include_partial(string $name, array $args = []): void
{
    echo '<div class="partial-' . $name . '">' . ($args['title'] ?? '') . ' | ' . ($args['content'] ?? '') . '</div>';
}

function esc_attr(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_url(mixed $url): string
{
    return (string) $url;
}

function sanitize_text_field(string $str): string
{
    return trim(strip_tags($str));
}

function get_page_by_path(string $path): object
{
    return (object) ['ID' => 1, 'post_name' => $path];
}

function get_permalink(object|int $post = 0): string
{
    $slug = is_object($post) && isset($post->post_name) ? $post->post_name : 'page';

    return 'https://wordpress.example.test/' . $slug . '/';
}

function add_query_arg(array $args, string $url): string
{
    return $url . '?' . http_build_query($args);
}

function get_template_directory_uri(): string
{
    return 'https://wordpress.example.test/theme';
}

function aif_turnstile_site_key(): string
{
    return 'site-key';
}

function verify_turnstile(): ?string
{
    return null;
}

function turnstile_friendly_error(string $error): string
{
    return $error;
}

function amnesty_logo(string $url = ''): void
{
}

/**
 * Accounts: $GLOBALS['__phpunit_wp_users'][email] = (object) [...]. Every
 * lookup is recorded so tests can check that a page does not probe accounts.
 */
function get_user_by(string $field, mixed $value): object|false
{
    $GLOBALS['__phpunit_user_lookups'][] = $value;

    return $GLOBALS['__phpunit_wp_users'][$value] ?? false;
}

function wp_hash_password(string $password): string
{
    return 'hashed:' . $password;
}

function wp_check_password(string $password, string $hash, int|string $user_id = ''): bool
{
    $GLOBALS['__phpunit_password_checks'][] = $hash;

    return $password === $hash;
}

function wp_signon(array $credentials, bool $secure = false): object
{
    return $GLOBALS['__phpunit_wp_users'][$credentials['user_login']];
}

function wp_set_current_user(int $id): void
{
}

function wp_insert_user(array $userdata): int
{
    $GLOBALS['__phpunit_inserted_users'][] = $userdata['user_email'];

    return 99;
}

function get_email_is_verified(int $user_id): bool
{
    return (bool) ($GLOBALS['__phpunit_verified_users'][$user_id] ?? false);
}

function store_SF_user_ID(int $user_id, string $sf_id): void
{
}

/**
 * Salesforce members: $GLOBALS['__phpunit_sf_members'][email] = (object) [...].
 */
function get_salesforce_member_data(string $email): object
{
    return $GLOBALS['__phpunit_sf_members'][$email] ?? (object) [];
}

function aif_validate_salesforce_member(mixed $member): mixed
{
    return $member;
}

function aif_is_salesforce_contact_absent(mixed $member): bool
{
    return is_object($member) && [] === get_object_vars($member);
}

function has_access_to_donation_space(mixed $member): bool
{
    return !empty($member->isDonateur) || !empty($member->isMembre);
}

function aif_log_salesforce_error(mixed $error): void
{
}

function get_salesforce_user_data(string $id): object
{
    return (object) ['FirstName' => 'Ada', 'LastName' => 'Lovelace'];
}

function aif_generate_random_hash(): string
{
    return 'token';
}

function store_email_token(int $user_id, string $token): void
{
}

function send_reset_password_email(string $email, string $url): void
{
    $GLOBALS['__phpunit_sent_emails'][] = $email;
}

function generate_2fa_code(): int
{
    return 123456;
}

function store_2fa_code(int $user_id, int $code): void
{
}

function get_2fa_code(int $user_id): string
{
    return '';
}

function send_2fa_code(string $email, int $code): bool
{
    $GLOBALS['__phpunit_sent_emails'][] = $email;

    return true;
}

function check_2fa_code(int $user_id, string $code): string
{
    return 'invalid';
}

function limit_login_attempts(int $user_id): bool
{
    return true;
}

function reset_login_attempts(int $user_id): void
{
}

function store_email_is_verified(int $user_id): void
{
}

function is_admin(): bool
{
    return false;
}

function has_post_thumbnail(): bool
{
    return false;
}

function has_block(string $block_name): bool
{
    return false;
}

function get_the_title(int $post_id = 0): string
{
    return 'FRANCE';
}

function esc_html(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function _e(string $text, string $domain = 'default'): void
{
    echo $text;
}

function get_template_directory(): string
{
    return $GLOBALS['__phpunit_template_directory'];
}

function get_local_user(string $email): mixed
{
    $GLOBALS['__phpunit_signer_lookups'][] = $email;

    return false;
}

function get_salesforce_user_with_email(string $email): mixed
{
    $GLOBALS['__phpunit_signer_lookups'][] = $email;

    return ['totalSize' => 0];
}

function is_email_token_valid(int $user_id, mixed $token): bool
{
    return false;
}

function delete_email_token(int $user_id): void
{
}

function wp_update_user(array $userdata): int
{
    return $userdata['ID'];
}
