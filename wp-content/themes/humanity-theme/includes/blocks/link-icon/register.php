<?php

declare( strict_types = 1 );

if (!function_exists('register_link_icon_block')) {
	/**
	 * Register the Link Icon block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_link_icon_block(): void {
		register_block_type(
			'amnesty-core/link-icon',
		);
	}
}
