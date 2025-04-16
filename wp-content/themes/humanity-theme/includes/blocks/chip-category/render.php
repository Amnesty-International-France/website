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

		$tag = !empty($attributes['link']) ? 'a' : 'div';

		return sprintf(
			'<%1$s class="chip-category %2$s %3$s"%4$s>%5$s</%1$s>',
			esc_attr($tag),                            // %1$s : tag = a ou div
			esc_attr($attributes['style']),            // %2$s : style class
			esc_attr($attributes['size']),             // %3$s : size class
			$tag === 'a' ? ' href="' . esc_url($attributes['link']) . '"' : '', // %4$s : href uniquement si <a>
			esc_html($attributes['label'])             // %5$s : contenu
		);


	}
}
