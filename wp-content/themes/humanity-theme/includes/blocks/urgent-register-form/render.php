<?php

declare(strict_types=1);

if ( ! function_exists( 'render_urgent_register_form_block' ) ) {

	/**
	 * Render Urgent Register Form Block
	 *
	 * @param array<string,mixed> $attributes the block attributes
	 *
	 * @return string
	 * @package Amnesty\Blocks
	 */
	function render_urgent_register_form_block( $attributes, $content, $block ): string {
		$input = $attributes['input'] ?? [];

		$args = [
			'input' => $input,
		];

		ob_start();
		$template_path = locate_template( 'partials/urgent-register-form.php' );

		if ( $template_path ) {
			extract( $args );
			include $template_path;
		} else {
			error_log( '❌ Template "partials/partials/urgent-register-form.php" introuvable' );
		}
		return ob_get_clean();
	}
}
