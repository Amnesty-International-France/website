<?php

declare(strict_types=1);


add_action('init', function () {
	if (is_singular()) return;

	$request_uri = $_SERVER['REQUEST_URI'];

	if (preg_match('#^/evenements/liste/page/(\d+)/?$#', $request_uri, $matches)) {
		$page = $matches[1];
		wp_redirect(home_url("/evenements/page/$page"), 301);
		exit;
	}

	// Optionnel : redirection /evenements/list → /evenements
	if ($request_uri === '/evenements/liste' || $request_uri === '/evenements/liste/') {
		wp_redirect(home_url("/evenements"), 301);
		exit;
	}
});
