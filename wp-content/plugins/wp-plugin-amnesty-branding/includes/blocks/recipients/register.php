<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Blocks\Recipients;

if ( ! function_exists( __NAMESPACE__ . '\\register_recipient_block' ) ) {
	/**
	 * Register Recipient block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_recipient_block(): void {
		register_block_type( 'amnesty-core/block-recipients' );
	}
}

add_action( 'init', __NAMESPACE__ . '\\register_recipient_block' );
