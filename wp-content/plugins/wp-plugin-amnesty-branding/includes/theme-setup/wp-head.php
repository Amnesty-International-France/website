<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_wp_title' ) ) {
	/**
	 * Format WP title
	 *
	 * @package Amnesty\ThemeSetup
	 *
	 * @param array<int,string> $parts original wp_title parts
	 *
	 * @return array
	 */
	function amnesty_wp_title( array $parts = [] ): array {
		/* translators: [front] */
		array_unshift( $parts, __( 'Amnesty International', 'aibrand' ) );

		return $parts;
	}
}

add_filter( 'wp_title_parts', __NAMESPACE__ . '\\amnesty_wp_title', 1 );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_favicons' ) ) {
	/**
	 * Output favicons for the theme
	 *
	 * @package Amnesty\ThemeSetup
	 *
	 * @return void
	 */
	function amnesty_favicons(): void {
		printf(
			'<link rel="apple-touch-icon" sizes="180x180" href="%s">',
			esc_url( plugins_url( '/assets/favicons/apple-touch-icon.png', AIBRAND_FILE ) )
		);

		printf(
			'<link rel="icon" type="image/png" sizes="32x32" href="%s">',
			esc_url( plugins_url( '/assets/favicons/favicon-32x32.png', AIBRAND_FILE ) )
		);

		printf(
			'<link rel="icon" type="image/png" sizes="16x16" href="%s">',
			esc_url( plugins_url( '/assets/favicons/favicon-16x16.png', AIBRAND_FILE ) )
		);

		printf(
			'<link rel="manifest" href="%s">',
			esc_url( plugins_url( '/assets/favicons/manifest.json', AIBRAND_FILE ) )
		);

		printf(
			'<link rel="mask-icon" href="%s" color="#5bbad5">',
			esc_url( plugins_url( '/assets/favicons/safari-pinned-tab.svg', AIBRAND_FILE ) )
		);

		echo '<meta name="msapplication-TileColor" content="#ffc40d">';
		echo '<meta name="theme-color" content="#ffffff">';
	}
}

add_action( 'wp_head', __NAMESPACE__ . '\\amnesty_favicons', 8 );
