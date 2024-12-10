<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

use WP_Block_Template;

/**
 * Load/unload custom block templates (FSE)
 */
class Block_Template_Loader {

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_filter( 'get_block_templates', [ $this, 'remove_woocommerce_templates' ], 12 );
	}

	/**
	 * Remove WooCommerce block templates
	 *
	 * @param array<int,WP_Block_Template> $templates the list of block templates
	 *
	 * @return array<int,WP_Block_Template>
	 */
	public function remove_woocommerce_templates( array $templates ) {
		foreach ( $templates as $index => $template ) {
			if ( str_starts_with( $template->id, 'woocommerce/' ) ) {
				unset( $templates[ $index ] );
			}
		}

		return array_values( $templates );
	}

}
