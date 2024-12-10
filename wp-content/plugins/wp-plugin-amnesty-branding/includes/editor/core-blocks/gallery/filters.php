<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Editor\Core_Blocks\Gallery;

if ( ! function_exists( __NAMESPACE__ . '\\remove_gallery_spacing_options' ) ) {
	/**
	 * Filter the metadata registration for the core/gallery block.
	 *
	 * @param array<string,mixed> $metadata The block metadata.
	 *
	 * @return array<string,mixed>
	 */
	function remove_gallery_spacing_options( $metadata ): array {
		if ( 'core/gallery' !== $metadata['name'] ) {
			return $metadata;
		}

		$metadata['supports']['spacing'] = false;

		return $metadata;
	}
}

add_filter( 'block_type_metadata', __NAMESPACE__ . '\\remove_gallery_spacing_options' );
