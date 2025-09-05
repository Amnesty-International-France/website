<?php

declare(strict_types=1);

if ( ! function_exists( 'register_latest_chronicle_promo_block' ) ) {
	/**
	 * Register the "Latest Chronicle Promo" block.
	 * This block finds and displays the latest chronicle post.
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_latest_chronicle_promo_block(): void {
		register_block_type( 'amnesty/latest-chronicle-promo', [
			'render_callback' => 'render_latest_chronicle_promo',
			'attributes'      => [],
		]);
	}
}
