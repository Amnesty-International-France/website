<?php

declare(strict_types=1);

if( !function_exists( 'register_chip_category_block' ) ) {
	/**
	 * Register Chip Category Block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_chip_category_block() : void {
		register_block_type(
			'amnesty-core/chip-category',
			[
				'render_callback' => 'render_chip_category_block',
				'attributes' => [
					'label' => [
						'type' => 'string',
						'default' => ''
					],
					'link' => [
						'type' => 'string',
						'default' => ''
					],
					'size' => [
						'type' => 'string',
						'default' => 'medium'
					],
					'style' => [
						'type' => 'string',
						"default" => 'bg-yellow'
					]
				]
			]
		);
	}
}
