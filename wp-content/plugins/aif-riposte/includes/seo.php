<?php

/**
 * Riposte SEO.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}


/**
 * Filter Yoast canonical for Riposte archive.
 *
 * @param string|null $canonical Canonical URL.
 *
 * @return string
 */
function aif_riposte_canonical(?string $canonical = ''): string
{
	if (! is_post_type_archive('riposte_victory')) {
		return function_exists('amnesty_normalise_canonical_host')
			? amnesty_normalise_canonical_host($canonical)
			: (string) $canonical;
	}

	$archive_url = get_post_type_archive_link('riposte_victory');

	if (! $archive_url) {
		return '';
	}

	$paged = max(1, (int) get_query_var('paged'));

	if ($paged > 1) {
		$archive_url = trailingslashit($archive_url) . 'page/' . $paged . '/';
	}

	return function_exists('amnesty_normalise_canonical_host')
		? amnesty_normalise_canonical_host($archive_url)
		: $archive_url;
}
add_filter('wpseo_canonical', 'aif_riposte_canonical');

/**
 * Filter Yoast title for Riposte archive.
 *
 * @param string $title SEO title.
 *
 * @return string
 */
function aif_riposte_wpseo_title(string $title): string
{
	if (! is_post_type_archive('riposte_victory')) {
		return $title;
	}

    return sprintf(
        '%s - %s',
        __('Ripostes', 'aif-riposte'),
        get_bloginfo('name')
    );
}
add_filter('wpseo_title', 'aif_riposte_wpseo_title');


/**
 * Filter social titles.
 *
 * @param string $title Current title.
 *
 * @return string
 */
function aif_riposte_social_title(string $title): string
{
	if (! is_post_type_archive('riposte_victory')) {
		return $title;
	}
    return sprintf(
        '%s - %s',
        __('Ripostes', 'aif-riposte'),
        get_bloginfo('name')
    );
}
add_filter('wpseo_opengraph_title', 'aif_riposte_social_title');
add_filter('wpseo_twitter_title', 'aif_riposte_social_title');