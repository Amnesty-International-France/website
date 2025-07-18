<?php

declare(strict_types=1);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'_VenueLatitude',
			'Latitude du lieux',
			'render_venue_latitude',
			'tribe_venue',
			'side',
			'default'
		);
	}
);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'_VenueLongitude',
			'Longitude du lieux',
			'render_venue_longitude',
			'tribe_venue',
			'side',
			'default'
		);
	}
);

add_action(
	'init',
	function () {
		register_post_meta(
			'tribe_venue',
			'_VenueLatitude',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);
	}
);

add_action(
	'init',
	function () {
		register_post_meta(
			'tribe_venue',
			'_VenueLongitude',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);
	}
);

function render_venue_latitude( $post ): void {
	$lat = get_post_meta( $post->ID, '_VenueLatitude', true );
	echo '<label for="venue_latitude">Latitude :</label><br />';
	echo '<input type="text" name="venue_latitude" value="' . esc_attr( $lat ) . '" style="width:100%;" />';
}

function render_venue_longitude( $post ): void {
	$lat = get_post_meta( $post->ID, '_VenueLongitude', true );
	echo '<label for="venue_longitude">Longitude :</label><br />';
	echo '<input type="text" name="venue_longitude" value="' . esc_attr( $lat ) . '" style="width:100%;" />';
}

add_action( 'save_post', 'auto_geocode_venue_address', 20, 1 );

function auto_geocode_venue_address( $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$nom_du_lieu = get_the_title( $post_id );

	if ( 'Partout en France' === $nom_du_lieu ) {
		return;
	}

	$address_parts = [
		get_post_meta( $post_id, '_VenueAddress', true ),
		get_post_meta( $post_id, '_VenueCity', true ),
		get_post_meta( $post_id, '_VenueState', true ),
		get_post_meta( $post_id, '_VenueZip', true ),
		get_post_meta( $post_id, '_VenueCountry', true ),
	];

	$full_address = implode( ', ', array_filter( $address_parts ) );

	if ( empty( $full_address ) ) {
		return;
	}

	$url = 'https://api-adresse.data.gouv.fr/search/?q=' . urlencode( $full_address );

	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return;
	}

	$json_response = json_decode( wp_remote_retrieve_body( $response ), true, 512, JSON_THROW_ON_ERROR );

	$location = $json_response['features'][0]['geometry']['coordinates'];

	if ( ! empty( $location ) ) {
		update_post_meta( $post_id, '_VenueLongitude', $location[0] );
		update_post_meta( $post_id, '_VenueLatitude', $location[1] );
	}
}
