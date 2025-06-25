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

add_action(
	'acf/include_fields',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_685bfd654d813',
				'title'                 => 'Partout En France',
				'fields'                => array(
					array(
						'key'               => 'field_685bfd654bfce',
						'label'             => 'Rendre l\'évènement national',
						'name'              => '_EventNational',
						'aria-label'        => '',
						'type'              => 'true_false',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'message'           => '',
						'default_value'     => 0,
						'allow_in_bindings' => 0,
						'ui_on_text'        => '',
						'ui_off_text'       => '',
						'ui'                => 1,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'tribe_events',
						),
					),
				),
				'menu_order'            => -100,
				'position'              => 'side',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
				'show_in_rest'          => 1,
			)
		);
	}
);

add_action( 'save_post', 'set_event_national', 20, 1 );

function set_event_national( $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$event = get_post( $post_id );

	$is_national = get_field( 'event_national', $post_id );

	if ( $is_national ) {
		update_post_meta( $post_id, 'event_national', $event->ID );
	}
}
