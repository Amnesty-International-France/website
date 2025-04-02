<?php

declare( strict_types = 1 );

if (!function_exists('register_image_block')) {
	/**
	 * Register the Image block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_image_block(): void {
		register_block_type(
			'amnesty-core/image',
		);
	}
}
