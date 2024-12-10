<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Editor\Block_Patterns;

use WP_Block_Patterns_Registry;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_remove_unbranded_block_patterns' ) ) {
	/**
	 * Remove all Block Patterns added by WP Core or 3rd party plugins
	 *
	 * @package Amnesty\Blocks\Patterns
	 *
	 * @return void
	 */
	function amnesty_remove_unbranded_block_patterns(): void {
		if ( ! is_admin() ) {
			return;
		}

		$registry = WP_Block_Patterns_Registry::get_instance();
		$patterns = $registry->get_all_registered();

		foreach ( $patterns as $pattern ) {
			if ( false === strpos( $pattern['name'], 'amnesty' ) ) {
				$registry->unregister( $pattern['name'] );
			}
		}
	}
}

add_action( 'admin_init', __NAMESPACE__ . '\\amnesty_remove_unbranded_block_patterns', 100 );
add_filter( 'should_load_remote_block_patterns', '__return_false' );
