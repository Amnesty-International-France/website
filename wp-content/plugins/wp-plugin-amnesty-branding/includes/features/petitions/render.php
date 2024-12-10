<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Features\Petitions;

add_filter( 'amnesty_petitions_template', __NAMESPACE__ . '\\amnesty_theme_petitions_layout' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_theme_petitions_layout' ) ) {
	/**
	 * Register the layout for a petition
	 *
	 * @package Amnesty
	 *
	 * @return array<int,array<int,string>>
	 */
	function amnesty_theme_petitions_layout(): array {
		return [
			[ 'amnesty-core/block-section' ],
			[ 'amnesty/petition-template' ],
			[ 'amnesty/petition' ],
		];
	}
}

add_filter( 'amnesty_petition_container', __NAMESPACE__ . '\\amnesty_theme_petitions_container_template' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_theme_petitions_container_template' ) ) {
	/**
	 * Override the template for the petitions container
	 *
	 * @package Amnesty
	 *
	 * @return string
	 */
	function amnesty_theme_petitions_container_template(): string {
		return locate_template( 'partials/petition-container.php' );
	}
}

add_filter( 'amnesty_petition_view_markup', __NAMESPACE__ . '\\amnesty_theme_petitions_view_markup' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_theme_petitions_view_markup' ) ) {
	/**
	 * Wrap the petitions markup in a container
	 *
	 * @package Amnesty
	 *
	 * @param string $markup the existing markup
	 *
	 * @return string
	 */
	function amnesty_theme_petitions_view_markup( string $markup = '' ): string {
		return sprintf( '<div class="article-sidebar">%s</div>', $markup );
	}
}
