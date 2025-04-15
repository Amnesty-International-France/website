<?php

declare( strict_types = 1 );

if (!function_exists('register_section_block')) {
	/**
	 * Register the Section block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_section_block(): void {
		register_block_type(
			'amnesty-core/section',
		);
	}
}
