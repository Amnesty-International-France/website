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
					'postId' => [
						'type' => 'integer',
						'default' => null,
					],
					'direction' => [
						'type' => 'string',
						'default' => 'portrait',
					],
					'title' => [
						'type' => 'string',
						'default' => 'Titre par défaut',
					],
					'permalink' => [
						'type' => 'string',
						'default' => '#',
					],
					'date' => [
						'type' => 'string',
						'default' => '',
					],
					'thumbnail' => [
						'type' => 'integer',
						'default' => '',
					],
					'main_category' => [
						'type' => [ 'object', 'string' ],
						'default' => null,
					],
					'terms' => [
						'type' => 'array',
						'default' => [],
						'items' => [
							'type' => 'object',
						],
					],
				],
			]
		);
	}
}
