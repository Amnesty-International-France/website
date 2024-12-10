<?php

// phpcs:disable PSR12.Files.FileHeader.IncorrectOrder

declare( strict_types = 1 );

/**
 * Add extra post types to campaign taxonomy
 */
add_filter(
	'amnesty_taxonomy_campaign_object_types',
	function ( array $types = [] ): array {
		return array_merge( $types, [ 'attachment', get_option( 'aip_petition_slug' ) ?: 'petition' ] );
	}
);

/**
 * Add extra post types to locations taxonomy
 */
add_filter(
	'amnesty_taxonomy_location_object_types',
	function ( array $types = [] ): array {
		return array_merge( $types, [ get_option( 'aip_petition_slug' ) ?: 'petition' ] );
	}
);

/**
 * Add extra post types to resource-type taxonomy
 */
add_filter(
	'amnesty_taxonomy_resource-type_object_types',
	function ( array $types = [] ): array {
		return array_merge( $types, [ get_option( 'aip_petition_slug' ) ?: 'petition' ] );
	}
);

/**
 * Add extra post types to topics type taxonomy
 */
add_filter(
	'amnesty_taxonomy_topic_object_types',
	function ( array $types = [] ): array {
		return array_merge( $types, [ 'attachment', get_option( 'aip_petition_slug' ) ?: 'petition' ] );
	}
);
