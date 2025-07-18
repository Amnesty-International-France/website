<?php

declare( strict_types = 1 );

if (!function_exists('register_action_block')) {
	/**
	 * Register the Action block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_action_block(): void {
		register_block_type(
			'amnesty-core/action',
		);
	}
}
