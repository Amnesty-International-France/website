<?php

declare( strict_types = 1 );

if (!function_exists('register_button_block')) {
	/**
	 * Register the button block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_button_block(): void {
		register_block_type(
			'amnesty-core/button',
		);
	}
}
