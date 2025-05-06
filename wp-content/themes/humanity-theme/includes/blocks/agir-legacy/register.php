<?php

declare(strict_types=1);

if (!function_exists('register_agir_legacy_block')) {
	/**
	 * Register the Agir Legacy block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_agir_legacy_block(): void {
		register_block_type(
			'amnesty-core/agir-legacy',
			[
				'render_callback' => 'render_agir_legacy_block',
				'attributes'      => [
					'postId' => [
						'type' => 'number',
					],
				],
			]
		);
	}
}
