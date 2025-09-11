<?php

declare(strict_types=1);

add_action(
    'init',
    function () {
        if (is_singular()) {
            return;
        }

        $request_uri  = $_SERVER['REQUEST_URI'];
        $query_string = $_SERVER['QUERY_STRING'] ?? '';

        $path = wp_parse_url($request_uri, PHP_URL_PATH);

        if (preg_match('#^/evenements/liste/page/(\d+)/?$#', $path, $matches)) {
            $page        = $matches[1];
            $redirect_to = home_url("/evenements/page/$page");


            if (! empty($query_string)) {
                $redirect_to .= '?' . $query_string;
            }
            wp_redirect($redirect_to, 301);
            exit;
        }

        // Optionnel : redirection /evenements/list → /evenements
        if (
            '/evenements/liste' === $request_uri ||
            '/evenements/liste/' === $request_uri ||
            '/evenements/liste/' . '?' . $query_string === $request_uri
        ) {
            $redirect_to = home_url('/evenements');
            if (! empty($query_string)) {
                $redirect_to .= '?' . $query_string;
            }
            wp_redirect($redirect_to, 301);
            exit;
        }
    }
);
