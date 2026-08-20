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
     * `expects_page` marks targets that must resolve to a published page. When such a
     * target is missing or unpublished we bail rather than emitting a cacheable 301 to a
     * URL that may 404. Non-page targets (e.g. an archive route) keep the path fallback.
     *
     * @return array<string,array{path:string,expects_page:bool}> Parent slug => target.
     */
    function amnesty_get_section_page_redirects(): array
    {
        return [
            'sinformer'      => ['path' => 'sinformer/articles', 'expects_page' => false],
            'agir-avec-nous' => ['path' => 'agir-avec-nous/comment-agir', 'expects_page' => true],
            'nous-soutenir'  => ['path' => 'nous-soutenir/don', 'expects_page' => true],
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

        $target_path  = $redirects[ $page->post_name ]['path'];
        $expects_page = $redirects[ $page->post_name ]['expects_page'];
        $target_page  = get_page_by_path($target_path);
        $is_published = $target_page && 'publish' === get_post_status($target_page);

        if ($is_published) {
            $target_url = get_permalink($target_page);
        } elseif ($expects_page) {
            // expected page is missing/unpublished: don't emit a cacheable 301 toward a 404.
            return;
        } else {
            $target_url = home_url('/' . trailingslashit($target_path));
        }

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

        // preserve the original query string (e.g. UTM params) so campaign attribution survives.
        if (! empty($_SERVER['QUERY_STRING'])) {
            wp_parse_str(wp_unslash($_SERVER['QUERY_STRING']), $query_args);

            if (! empty($query_args)) {
                $target_url = add_query_arg($query_args, $target_url);
            }
        }

        wp_safe_redirect($target_url, 301);
        exit;
    }
}

add_action('template_redirect', 'amnesty_redirect_empty_section_pages', 1);
