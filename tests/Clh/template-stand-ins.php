<?php

declare(strict_types=1);

// Rendering stubs loaded only by the isolated template tests.
function get_template_part(string $slug, ?string $name = null, array $args = []): void
{
    require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/' . $slug . '.php';
}

function amnesty_get_clh_petition_tunnel_url(): string
{
    return '/changez-leur-histoire/campagne/';
}

function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_attr(string $text): string
{
    return esc_html($text);
}

function esc_url(string $text): string
{
    return esc_html($text);
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return esc_html($text);
}

function esc_html_e(string $text, string $domain = 'default'): void
{
    echo esc_html($text);
}

function esc_attr_e(string $text, string $domain = 'default'): void
{
    echo esc_attr($text);
}

function _n(string $singular, string $plural, int $number, string $domain = 'default'): string
{
    return $number > 1 ? $plural : $singular;
}

function number_format_i18n(int $number): string
{
    return number_format($number, 0, ',', ' ');
}
