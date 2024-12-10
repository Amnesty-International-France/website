<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Features\Death_Penalty;

use CMB2;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_custom_fields_taxonomy_location' ) ) {
	/**
	 * Register custom fields for the Locations taxonomy
	 *
	 * @package Amnesty
	 *
	 * @param CMB2 $metabox the cmb2 object
	 *
	 * @return void
	 */
	function amnesty_custom_fields_taxonomy_location( CMB2 $metabox ): void {
		/* translators: [admin] */
		amnesty_cmb2_wrap_open(
			$metabox,
			__( 'Death Penalty', 'aibrand' ),
			false,
			false,
			[
				'show-on' => 'type',
				'show-if' => 'default',
			]
		);

		$metabox->add_field(
			[
				'id'               => 'death_penalty',
				/* translators: [admin] */
				'name'             => __( 'Status in Law', 'aibrand' ),
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => get_death_penalty_status_labels(),
			]
		);

		amnesty_cmb2_wrap_close( $metabox );
	}
}

add_action( 'taxonomy_location_custom_fields', __NAMESPACE__ . '\\amnesty_custom_fields_taxonomy_location', 10 );

register_rest_field(
	'location',
	'death_penalty_status',
	[
		'get_callback' => function ( array $term ) {
			return get_death_penalty_status( (object) $term );
		},
	]
);
