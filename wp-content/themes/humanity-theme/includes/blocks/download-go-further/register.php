<?php

declare( strict_types = 1 );

if (!function_exists('register_download_go_further_block')) {
	/**
	 * Register the Download Go Further block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_download_go_further_block(): void {
		register_block_type(
			'amnesty-core/download-go-further',
		);
	}
}
