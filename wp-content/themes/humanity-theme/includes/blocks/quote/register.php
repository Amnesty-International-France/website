<?php

declare(strict_types=1);

if (!function_exists('register_quote_block')) {
	/**
	 * Register the Quote block
	 *
	 * @return void
	 */
	function register_quote_block(): void {
		register_block_type(
			'amnesty-core/blockquote',
			[
				'render_callback' => 'render_quote_block',
				'attributes'      => [
					'quoteText' => [
						'type'    => 'string',
						'default' => 'Saisissez votre citation',
					],
					'author' => [
						'type'    => 'string',
						'default' => "Indiquez l'auteur",
					],
					'showImage' => [
						'type'    => 'boolean',
						'default' => false,
					],
					'imageId' => [
						'type'    => 'number',
						'default' => null,
					],
					'bgColor' => [
						'type'    => 'string',
						'default' => 'black',
					],
					'size' => [
						'type'    => 'string',
						'default' => 'medium',
					],
				],
			]
		);
	}
}
