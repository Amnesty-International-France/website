<?php

declare(strict_types=1);

if (!function_exists('render_article_card_block')) {

	/**
	 * Render Article Card Block
	 *
	 * @param array<string,mixed> $attributes the block attributes
	 *
	 * @return string
	 * @package Amnesty\Blocks
	 *
	 */
	function render_article_card_block($attributes, $content, $block)
	{
		$post = get_post();
		if (!$post) {
			return '';
		}

		ob_start();
		get_template_part('partials/article-card', null, [
			'post' => $post,
			'direction' => $attributes['direction'] ?? 'portrait',
		]);
		return ob_get_clean();
	}
}
