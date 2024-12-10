<?php

declare( strict_types = 1 );

// skip output of donate item in mobile nav
add_filter(
	'amnesty_mobile_nav_menu_item_skip',
	function ( bool $skip, mixed $item ): bool {
		return in_array( 'donate-menu-item', $item->classes ?? [], true );
	},
	10,
	2
);
