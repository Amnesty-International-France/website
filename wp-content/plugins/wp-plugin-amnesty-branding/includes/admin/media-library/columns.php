<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Admin\Media_Library;

if ( ! function_exists( __NAMESPACE__ . '\\add_amnesty_column_to_media_library' ) ) {
	/**
	 * Add Amnesty-specific info to the media library
	 *
	 * @package Amnesty\Admin\Options
	 *
	 * @param array<string,string> $columns the existing columns
	 *
	 * @return array<string,string>
	 */
	function add_amnesty_column_to_media_library( array $columns = [] ): array {
		if ( isset( $columns['amnesty'] ) ) {
			return $columns;
		}

		return array_slice( $columns, 0, 2 ) + [ 'amnesty' => /* translators: [admin] */ __( 'Amnesty Info', 'aibrand' ) ] + array_slice( $columns, 2 );
	}
}

add_filter( 'manage_media_columns', __NAMESPACE__ . '\\add_amnesty_column_to_media_library' );

if ( ! function_exists( __NAMESPACE__ . '\\render_amnesty_media_library_column' ) ) {
	/**
	 * Render Amnesty-specific info on Media list table
	 *
	 * @package Amnesty\Admin\Options
	 *
	 * @param string $name the name of the column to render
	 * @param int    $id   the current media item ID
	 *
	 * @return void
	 */
	function render_amnesty_media_library_column( string $name, int $id ) {
		if ( 'amnesty' !== $name ) {
			return;
		}

		if ( false === strpos( get_post_field( 'post_mime_type', $id ), 'image' ) ) {
			return;
		}

		$caption  = wp_get_attachment_caption( $id );
		$alt_text = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$credit   = get_post_field( 'post_content', $id );

		$output = '<table class="nopad" role="presentation"><tbody>';

		if ( $caption ) {
			$output .= sprintf( '<tr><td>%s</td></tr>', /* translators: [admin] */ esc_html__( 'Has Caption', 'aibrand' ) );
		}

		if ( $alt_text ) {
			$output .= sprintf( '<tr><td>%s</td></tr>', /* translators: [admin] */ esc_html__( 'Has Alt Text', 'aibrand' ) );
		}

		if ( $credit ) {
			$output .= sprintf( '<tr><td>%s</td></tr>', /* translators: [admin] */ esc_html__( 'Has Credit', 'aibrand' ) );
		}

		$output .= '</tbody></table>';

		echo wp_kses_post( $output );
	}
}

add_action( 'manage_media_custom_column', __NAMESPACE__ . '\\render_amnesty_media_library_column', 10, 2 );
