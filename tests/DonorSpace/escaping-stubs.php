<?php

declare(strict_types=1);

/**
 * Stand-ins for the WordPress escaping functions used by the donor space
 * templates, close enough to WordPress for a test to tell escaped output from
 * raw output.
 */

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

if (!function_exists('esc_url')) {
    /**
     * Like WordPress: drops the characters a URL cannot hold (quotes, angle
     * brackets, spaces...), empties URLs with another protocol than http(s) or
     * mailto, and encodes & and ' for HTML.
     */
    function esc_url(mixed $url): string
    {
        $url = (string) preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\x80-\xff]|i', '', (string) $url);
        if (1 === preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $scheme) && !in_array(strtolower($scheme[1]), ['http', 'https', 'mailto'], true)) {
            return '';
        }

        return str_replace(['&', "'"], ['&#038;', '&#039;'], $url);
    }
}

if (!function_exists('wp_kses_post')) {
    /**
     * Keeps links, the only markup the donor space messages use, minus their
     * event handlers and their non-http(s)/mailto URLs, and strips every other
     * tag. Unlike the real wp_kses_post(), it also strips <img>.
     */
    function wp_kses_post(string $text): string
    {
        $text = strip_tags($text, ['a']);
        $text = (string) preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $text);
        return (string) preg_replace('~\shref\s*=\s*("\s*(?!https?:|mailto:|[/#])[^"]*"|\'\s*(?!https?:|mailto:|[/#])[^\']*\'|(?!["\']|https?:|mailto:|[/#])[^\s>]+)~i', '', $text);
    }
}
