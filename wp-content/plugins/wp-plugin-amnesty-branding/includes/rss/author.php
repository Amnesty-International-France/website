<?php

declare( strict_types = 1 );

add_filter(
	'the_author',
	function ( $name = '' ) {
		if ( is_feed() ) {
			return get_bloginfo( 'name' );
		}

		return $name;
	}
);
