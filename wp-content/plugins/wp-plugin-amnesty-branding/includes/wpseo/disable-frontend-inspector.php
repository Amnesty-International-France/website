<?php

declare( strict_types = 1 );

add_action(
	'wp_loaded',
	function () {
		wp_deregister_script( 'yoast-seo-premium-frontend-inspector' );
	},
	12
);
