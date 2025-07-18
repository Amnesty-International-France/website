<?php

declare( strict_types = 1 );

if (!function_exists('register_chapo_block')) {
	/**
	 * Register the Chapo block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_chapo_block(): void {
		register_block_type(
			'amnesty-core/chapo',
		);
	}
}
