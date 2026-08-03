<?php

declare(strict_types=1);

if (! function_exists('amnesty_get_section_page_redirects')) {
    /**
     * Map contentless top-level section pages to the child page they should redirect to.
     *
     * These pages only exist to hold the site tree and their menu entry: they have no
     * content of their own, so any hit on them (breadcrumb, menu, footer, direct URL)
     * lands on an empty page showing nothing but the title.
     *
     * @package Amnesty\Permalinks
     *
     * @return array<string,string> Parent page slug => target page path.
     */
    function amnesty_get_section_page_redirects(): array
    {
        return [
            'sinformer'      => 'sinformer/articles',
            'agir-avec-nous' => 'agir-avec-nous/comment-agir',
            'nous-soutenir'  => 'nous-soutenir/don',
        ];
    }
}

if (! function_exists('amnesty_redirect_empty_section_pages')) {
    /**
     * Permanently redirect contentless top-level section pages to their first child.
     *
     * @package Amnesty\Permalinks
     *
     * @return void
     */
    function amnesty_redirect_empty_section_pages(): void
    {
        // never interfere with the editor, previews or non-HTML requests.
        if (is_admin() || wp_doing_ajax() || is_feed() || is_preview() || is_customize_preview()) {
            return;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        if (! is_page()) {
            return;
        }

        $page = get_queried_object();

        // only top-level pages, so a child page sharing the slug is never caught.
        if (! $page instanceof WP_Post || 0 !== (int) $page->post_parent) {
            return;
        }

        $redirects = amnesty_get_section_page_redirects();

        if (! isset($redirects[ $page->post_name ])) {
            return;
        }

        $target_path = $redirects[ $page->post_name ];
        $target_page = get_page_by_path($target_path);
        $target_url  = $target_page && 'publish' === get_post_status($target_page)
            ? get_permalink($target_page)
            : home_url('/' . trailingslashit($target_path));

        if (! $target_url) {
            return;
        }

        $current_url = get_permalink($page);

        // bail rather than loop if the target resolves back to this page.
        if (
            $current_url
            && function_exists('amnesty_url_paths_match')
            && amnesty_url_paths_match($target_url, $current_url)
        ) {
            return;
        }

        wp_safe_redirect($target_url, 301);
        exit;
    }
}

add_action('template_redirect', 'amnesty_redirect_empty_section_pages', 1);
