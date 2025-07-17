<?php

declare(strict_types=1);

add_action( 'rest_api_init', 'amnesty_register_local_structures_rest_route' );

function amnesty_register_local_structures_rest_route() {
    register_rest_route( 'amnesty/v1', '/local-structures-search', array(
        'methods'             => 'GET',
        'callback'            => 'amnesty_get_local_structures_by_bounds',
        'permission_callback' => '__return_true',
        'args'                => array(
            'south' => array(
                'required'          => true,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
            'west'  => array(
                'required'          => true,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
            'north' => array(
                'required'          => true,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
            'east'  => array(
                'required'          => true,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
        ),
    ) );
}

/**
 * Récupère les structures locales dans des limites géographiques données.
 *
 * @param WP_REST_Request $request La requête REST.
 * @return WP_REST_Response Les données des marqueurs.
 */
function amnesty_get_local_structures_by_bounds( $request ) {
    $south = (float) $request['south'];
    $west  = (float) $request['west'];
    $north = (float) $request['north'];
    $east  = (float) $request['east'];

    $args = array(
        'post_type'      => 'local-structures',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'latitude',
                'value'   => array( $south, $north ),
                'type'    => 'DECIMAL(10,6)',
                'compare' => 'BETWEEN',
            ),
            array(
                'key'     => 'longitude',
                'value'   => array( $west, $east ),
                'type'    => 'DECIMAL(10,6)',
                'compare' => 'BETWEEN',
            ),
        ),
    );

    $local_structures_ids = get_posts( $args );
    $markers_data = [];

    foreach ( $local_structures_ids as $post_id ) {
        $latitude  = get_field( 'latitude', $post_id );
        $longitude = get_field( 'longitude', $post_id );
        $city      = get_field( 'ville', $post_id );
        $address   = get_field( 'adresse', $post_id );
        $phone     = get_field( 'telephone', $post_id );
        $email     = get_field( 'email', $post_id );

        $post_title = get_the_title( $post_id );
        $post_url = get_permalink( $post_id );

        $thumbnail_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );

        $markers_data[] = [
            'id'       => $post_id,
            'title'    => $post_title,
            'url'      => $post_url,
            'image'    => $thumbnail_url ? $thumbnail_url : '',
            '_geoloc'  => [
                'lat' => (float) $latitude,
                'lng' => (float) $longitude,
            ],
            'city'     => $city,
            'address'  => $address,
            'phone'    => $phone,
            'email'    => $email,
            'facet'    => '',
            'subfacet' => '',
        ];
    }

    return new WP_REST_Response( $markers_data, 200 );
}

add_action( 'rest_api_init', 'amnesty_register_geocode_proxy_rest_route' );

function amnesty_register_geocode_proxy_rest_route() {
    register_rest_route( 'amnesty/v1', '/geocode-proxy', array(
        'methods'             => 'GET',
        'callback'            => 'amnesty_geocode_proxy_callback',
        'permission_callback' => '__return_true',
        'args'                => array(
            'address' => array(
                'required'          => true,
                'validate_callback' => function( $param ) {
                    return is_string( $param ) && ! empty( $param );
                },
            ),
        ),
    ) );
}

function amnesty_geocode_proxy_callback( $request ) {
    $google_api_key = 'AIzaSyCem4iHlqaAN1mS72Lst9khlEFOERGdQHE';

    $address = sanitize_text_field( $request['address'] );
    $google_geocode_url = add_query_arg(
        array(
            'address' => urlencode( $address ),
            'key'     => $google_api_key,
        ),
        'https://maps.googleapis.com/maps/api/geocode/json'
    );

    $response = wp_remote_get( $google_geocode_url );

    if ( is_wp_error( $response ) ) {
        return new WP_REST_Response( array( 'error' => 'Failed to fetch geocoding data.' ), 500 );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    return new WP_REST_Response( $data, 200 );
}
