<?php

declare( strict_types = 1 );

if (!function_exists('register_get_informed_block')) {
	/**
	 * Register the Get Informed block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_get_informed_block(): void {
		register_block_type('amnesty-core/get-informed', [
			'render_callback' => 'render_get_informed_block',
			'attributes' => [
				'links' => [
					'type' => 'array',
					'default' => [],
				],
			],
		]);
	}
}
