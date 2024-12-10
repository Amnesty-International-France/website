<?php

declare( strict_types = 1 );

if ( ! function_exists( 'amnesty_cmb2_wrap_open' ) ) {
	/**
	 * Output a fake field to open a group wrapper
	 *
	 * @package Amnesty\Plugins\CMB2
	 *
	 * @param mixed  $metabox the metabox object
	 * @param string $title   the wrapper's title
	 * @param bool   $closed  whether wrapper is collapsed
	 * @param bool   $hidden  whether wrapper is hidden
	 * @param array  $data    any data attributes to add
	 *
	 * @return void
	 */
	function amnesty_cmb2_wrap_open( $metabox, string $title = '', bool $closed = false, bool $hidden = false, array $data = [] ) {
		$metabox->add_field(
			[
				'id'            => random_bytes( 4 ),
				'type'          => 'noop',
				'render_row_cb' => fn () => amnesty_cmb2_wrap_open_row_callback( $title, $closed, $hidden, $data ),
			]
		);
	}
}

if ( ! function_exists( 'amnesty_cmb2_wrap_close' ) ) {
	/**
	 * Output a fake field to close a group wrapper
	 *
	 * @package Amnesty\Plugins\CMB2
	 *
	 * @param mixed $metabox the metabox object
	 *
	 * @return void
	 */
	function amnesty_cmb2_wrap_close( $metabox ) {
		$metabox->add_field(
			[
				'id'            => random_bytes( 4 ),
				'type'          => 'noop',
				'render_row_cb' => function () {
					echo '</div></div>';
				},
			]
		);
	}
}
