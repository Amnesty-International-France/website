<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Frontend;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_font_face_css' ) ) {
	/**
	 * Enqueue the amnesty font face declarations and CSS
	 *
	 * @return string
	 */
	function amnesty_font_face_css(): string {
		return ':root{
			--wp--preset--font-family--primary:"Amnesty Trade Gothic",sans-serif;
			--wp--preset--font-family--secondary:"Amnesty Trade Gothic Condensed",sans-serif;
		}';
	}
}
add_filter( 'amnesty_font_face_css', __NAMESPACE__ . '\\amnesty_font_face_css' );
