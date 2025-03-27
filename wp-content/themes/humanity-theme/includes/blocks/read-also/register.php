<?php

declare( strict_types = 1 );

if (!function_exists('register_small_section_block')) {
	/**
	 * Register the Small Section block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_small_section_block(): void {
		register_block_type(
			'amnesty-core/small-section',
		);
	}
}
