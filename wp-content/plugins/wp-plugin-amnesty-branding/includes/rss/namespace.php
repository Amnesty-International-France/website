<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\RSS;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_rss2_namespace' ) ) {
	/**
	 * Add an XML namespace for Amnesty
	 *
	 * @package Amnesty\RSS
	 *
	 * @return void
	 */
	function amnesty_rss2_namespace() {
		printf( 'xmlns:amn="%s"', esc_url( network_home_url() ) );
	}
}

add_action( 'rss2_ns', __NAMESPACE__ . '\\amnesty_rss2_namespace' );
