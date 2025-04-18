<?php

declare(strict_types=1);

if (!function_exists('register_article_card_block')) {
	/**
	 * Register Article Card Block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_article_card_block(): void
	{
		register_block_type(
			'amnesty-core/article-card',
			[
				'render_callback' => 'render_article_card_block',
				'attributes' => [
					'direction' => [
						'type' => 'string',
						'default' => 'portrait' //portrait ou landscape
					],
				]
			]
		);
	}
}
