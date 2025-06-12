<?php

declare(strict_types=1);

if (!function_exists('register_hero_homepage_block')) {
	/**
	 * Register the Hero Homepage block
	 */
	function register_hero_homepage_block(): void {
		register_block_type(
			'amnesty-core/hero-homepage',
			[
				'render_callback' => 'render_hero_homepage_block',
				'attributes'      => [
					'items' => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type' => 'object',
						],
					],
					'className' => [
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);
	}
}
