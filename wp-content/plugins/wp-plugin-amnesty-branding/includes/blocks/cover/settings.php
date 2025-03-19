<?php

namespace Amnesty\Branding\Blocks\Cover;

add_filter( 'block_type_metadata', __NAMESPACE__ . '\\amnesty_filter_cover_block_metadata' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_filter_cover_block_metadata' ) ) {
	/**
	 * Filter the core/cover block metadata
	 *
	 * @param array<string,mixed> $metadata the block metadata
	 *
	 * @return array<string,mixed>
	 */
	function amnesty_filter_cover_block_metadata( array $metadata ): array {
		if ( ! isset( $metadata['name'] ) || 'core/cover' !== $metadata['name'] ) {
			return $metadata;
		}

		unset( $metadata['supports']['color']['__experimentalDuotone'] );

		return $metadata;
	}
}
