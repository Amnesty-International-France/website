<?php

declare(strict_types=1);

if (!function_exists('register_read_also_block')) {
	/**
	 * Register the Read Also block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_read_also_block(): void {
		register_block_type(
			'amnesty-core/read-also',
			[
				'render_callback' => 'render_read_also_block',
				'attributes'      => [
					'postId' => [
						'type' => 'number',
					],
				],
			]
		);
	}
}
