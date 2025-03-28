<?php

declare( strict_types = 1 );

if( ! function_exists( 'render_chip_category_block' ) ) {

	/**
	 * Render Chip Category Block
	 *
	 * @param array<string,mixed> $attributes the block attributes
	 *
	 * @return string
	 * @package Amnesty\Blocks
	 *
	 */
	function render_chip_category_block( array $attributes ) : string {
		$attributes = wp_parse_args(
			$attributes,
			[
				'label' => '',
				'link' => '',
				'size' => 'medium',
				'style' => 'bg-yellow',
			]
		);

		return sprintf(
			'<a class="chip-category %s %s" href="%s">%s</a>',
			esc_attr($attributes['style']),
			esc_attr($attributes['size']),
			esc_url($attributes['link']),
			esc_html($attributes['label'])
		);
	}
}
