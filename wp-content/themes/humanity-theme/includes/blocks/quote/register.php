<?php

declare( strict_types = 1 );

if (!function_exists('register_quote_block')) {
	/**
	 * Register the Quote block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_quote_block(): void {
		register_block_type(
			'amnesty-core/blockquote',
		);
	}
}
