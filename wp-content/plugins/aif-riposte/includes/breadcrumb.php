<?php

/**
 * Riposte breadcrumb.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Add "S'informer" parent to Yoast breadcrumb.
 *
 * @param array<int,array<string,mixed>> $links Breadcrumb links.
 *
 * @return array<int,array<string,mixed>>
 */
function aif_riposte_yoast_breadcrumb_links(array $links): array
{
	if (! is_post_type_archive('riposte_victory')) {
		return $links;
	}

	$parent = [
		'text' => __('S’informer', 'aif-riposte'),
		'url'  => '#',
	];

	$last_position = max(count($links) - 1, 1);

	array_splice($links, $last_position, 0, [ $parent ]);

	return $links;
}
add_filter('wpseo_breadcrumb_links', 'aif_riposte_yoast_breadcrumb_links');