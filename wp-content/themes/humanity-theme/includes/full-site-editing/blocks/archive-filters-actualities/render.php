<?php

declare( strict_types = 1 );

if ( ! function_exists( 'render_archive_filters_actualities_block' ) ) {
	/**
	 * Render the archive filters actualities block
	 *
	 * @return string
	 */
	function render_archive_filters_actualities_block(): string {
		spaceless();

		echo '<div class="section section--tinted">';
		get_template_part( 'partials/archive/filters-actualities' );
		echo '</div>';

		return endspaceless( false );
	}
}
