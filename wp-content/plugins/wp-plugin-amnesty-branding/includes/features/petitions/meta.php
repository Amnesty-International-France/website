<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Features\Petitions;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_theme_register_petitions_meta' ) ) {
	/**
	 * Register theme metadata with petitions post type
	 *
	 * @package Amnesty
	 *
	 * @return void
	 */
	function amnesty_theme_register_petitions_meta(): void {
		$slug = get_option( 'aip_petition_slug' ) ?: 'petition';

		// post type isn't registered
		if ( ! get_post_type( $slug ) ) {
			return;
		}

		$args = [
			'show_in_rest'  => true,
			'single'        => true,
			'auth_callback' => fn () => current_user_can( 'edit_posts' ),
		];

		register_meta( $slug, '_hero_title', $args );
		register_meta( $slug, '_hero_content', $args );
		register_meta( $slug, '_hero_cta_text', $args );
		register_meta( $slug, '_hero_cta_link', $args );
		register_meta( $slug, '_hero_alignment', $args );
		register_meta( $slug, '_hero_background', $args );
		register_meta( $slug, '_hero_size', $args );
		register_meta( $slug, '_hero_show', $args );
		register_meta( $slug, '_hero_type', $args );
		register_meta( $slug, '_hero_embed', $args );
		register_meta( $slug, '_hero_video_id', $args + [ 'type' => 'integer' ] );
	}
}

add_action( 'init', __NAMESPACE__ . '\\amnesty_theme_register_petitions_meta' );
