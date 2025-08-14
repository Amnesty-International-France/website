<?php

declare( strict_types = 1 );

if (!function_exists('register_card_image_text_block')) {
	/**
	 * Register the Card Image Text block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function register_card_image_text_block(): void {
		register_block_type('amnesty-core/card-image-text', [
			'render_callback' => 'render_card_image_text_block',
			'attributes' => [
				'custom' => [
					'type' => 'boolean',
					'default' => false,
				],
				'direction' => [
					'type' => 'string',
					'default' => 'vertical',
				],
				'postId' => [
					'type' => 'integer',
					'default' => null,
				],
				'title' => [
					'type' => 'string',
					'default' => 'Titre par défaut',
				],
				'subtitle' => [
					'type' => 'string',
					'default' => 'Sous-titre par défaut',
				],
				'category' => [
					'type' => 'string',
					'default' => 'Categorie',
				],
				'permalink' => [
					'type' => 'string',
					'default' => '#',
				],
				'thumbnail' => [
					'type' => 'integer',
					'default' => null,
				],
				'text' => [
					'type' => 'string',
					'default' => 'Texte par défaut',
				],
				'selectedPostCategorySlug' => [
                    'type' => 'string',
                    'default' => '',
                ]
			],
		]);
	}
}
