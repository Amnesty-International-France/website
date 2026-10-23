<?php

declare(strict_types=1);

final class AccountVerificationRedirect extends RuntimeException
{
}

function wp_redirect(string $location, int $status = 302): never
{
    throw new AccountVerificationRedirect($location);
}

function get_header(): void
{
}

function get_footer(): void
{
}

function get_template_directory_uri(): string
{
    return 'https://wordpress.example.test/theme';
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

if (!function_exists('wp_unslash')) {
    function wp_unslash(string $value): string
    {
        return stripslashes($value);
    }
}

function get_page_by_path(string $path): object
{
    // One ID per slug, so get_permalink() (tests/bootstrap.php) resolves each page.
    $id = crc32($path);
    $GLOBALS['__phpunit_post_permalinks'][$id] ??= 'https://wordpress.example.test/' . $path . '/';

    return (object) ['ID' => $id, 'post_name' => $path];
}

function aif_turnstile_site_key(): string
{
    return 'site-key';
}

function verify_turnstile(): ?string
{
    return null;
}

function get_user_by(string $field, string $value): WP_User|false
{
    foreach ($GLOBALS['__phpunit_users'] as $user) {
        if ('email' === $field && 0 === strcasecmp($user->user_email, $value)) {
            return $user;
        }
    }

    return false;
}

function wp_insert_user(array $userdata): int
{
    $GLOBALS['__phpunit_inserted_users'][] = $userdata;
    $GLOBALS['__phpunit_users'][42] = new WP_User(
        [$userdata['role']],
        42,
        $userdata['user_email'],
        $userdata['user_login'],
        '',
        $userdata['user_pass']
    );

    return 42;
}

function wp_generate_password(int $length = 12, bool $special_chars = true, bool $extra_special_chars = false): string
{
    return 'random-password-' . $length;
}

function get_salesforce_access_token_donor_space_donor_space(): string
{
    return 'access-token';
}

function wp_remote_post(string $url, array $args = []): mixed
{
    $GLOBALS['__phpunit_sent_mails'][] = $args['body'];

    return $GLOBALS['__phpunit_mail_response'];
}
