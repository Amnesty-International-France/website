<?php

declare(strict_types=1);

if ( ! function_exists( 'register_donation_calculator_block' ) ) {
	/**
	 * Register Event Card Block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_donation_calculator_block(): void {
		register_block_type(
			'amnesty-core/donation-calculator',
			[
				'render_callback' => 'render_donation_calculator_block',
				'attributes'      => [
					'size'        => [
						'type'    => 'string',
						'default' => '',
					],
					'with_header' => [
						'type'    => 'boolean',
						'default' => false,
					],
					'with_tabs'   => [
						'type'    => 'boolean',
						'default' => false,
					],
					'with_legend' => [
						'type'    => 'boolean',
						'default' => false,
					],
					'href'        => [
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);
	}
}
