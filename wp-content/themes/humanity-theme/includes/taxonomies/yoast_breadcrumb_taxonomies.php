<?php

function custom_yoast_breadcrumb_for_all_taxonomies( $links ) {

	if ( is_tax() ) {

		$current_taxonomy_slug = get_query_var( 'taxonomy' );
		$taxonomy_object = get_taxonomy( $current_taxonomy_slug );

		if ( ! $taxonomy_object ) {
			return $links;
		}

		$breadcrumb_to_add = [
			[
				'text' => $taxonomy_object->labels->name,
				'url'  => '#',
			],
		];

		array_splice( $links, 1, 0, $breadcrumb_to_add );
	}

	return $links;
}

add_filter( 'wpseo_breadcrumb_links', 'custom_yoast_breadcrumb_for_all_taxonomies' );
