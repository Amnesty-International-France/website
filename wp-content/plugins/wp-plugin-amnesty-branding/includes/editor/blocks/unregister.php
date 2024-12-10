<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Editor\Blocks;

use WP_Block_Type_Registry;

if ( ! function_exists( __NAMESPACE__ . '\\unregister_woocommerce_blocks' ) ) {
	/**
	 * Unregister all registered WooCommerce blocks
	 *
	 * @return void
	 */
	function unregister_woocommerce_blocks(): void {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $name => $block ) {
			if ( str_starts_with( $name, 'woocommerce' ) ) {
				$registry->unregister( $name );
			}
		}
	}
}

add_action( 'wp_loaded', __NAMESPACE__ . '\\unregister_woocommerce_blocks' );
