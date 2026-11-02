<?php

declare(strict_types=1);

if (!function_exists('esc_attr')) {
    function esc_attr(mixed $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html(mixed $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('_e')) {
    function _e(string $text, string $domain = 'default'): void
    {
        echo $text;
    }
}

if (!function_exists('aif_include_partial')) {
    function aif_include_partial(string $name, array $args = []): void
    {
    }
}

if (!function_exists('get_page_by_path')) {
    function get_page_by_path(string $path): object
    {
        return (object) ['ID' => 1, 'post_name' => $path];
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field(int|string $action = -1, string $name = '_wpnonce'): void
    {
    }
}

if (!function_exists('has_post_thumbnail')) {
    function has_post_thumbnail(): bool
    {
        return false;
    }
}

if (!function_exists('has_block')) {
    function has_block(string $block_name): bool
    {
        return false;
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title(int $post_id = 0): string
    {
        return 'FRANCE';
    }
}

if (!function_exists('aif_turnstile_site_key')) {
    function aif_turnstile_site_key(): string
    {
        return 'site-key';
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return false;
    }
}

if (!function_exists('get_local_user')) {
    function get_local_user(string $email): mixed
    {
        return false;
    }
}

if (!function_exists('get_salesforce_user_with_email')) {
    function get_salesforce_user_with_email(string $email): mixed
    {
        return ['totalSize' => 0];
    }
}

if (!function_exists('get_template_directory')) {
    function get_template_directory(): string
    {
        return $GLOBALS['__phpunit_template_directory'];
    }
}
