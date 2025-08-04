<?php

declare(strict_types=1);

add_action( 'admin_init', 'amnesty_register_map_settings' );

function amnesty_register_map_settings() {
    add_settings_section(
        'amnesty_map_api_settings_section',
        'Réglages de la Carte Interactive',
        'amnesty_map_api_settings_section_callback',
        'general'
    );

    add_settings_field(
        'amnesty_Maps_api_key',
        'Clé API Google Maps',
        'amnesty_Maps_api_key_callback',
        'general',
        'amnesty_map_api_settings_section'
    );

    register_setting(
        'general',
        'amnesty_Maps_api_key',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest'      => false,
            'default'           => '',
        )
    );
}

function amnesty_map_api_settings_section_callback() {
    echo '<p>Saisissez votre clé API Google Maps pour la fonctionnalité de carte interactive.</p>';
}

function amnesty_Maps_api_key_callback() {
    $api_key = get_option( 'amnesty_Maps_api_key' );
    echo '<input type="text" id="amnesty_Maps_api_key" name="amnesty_Maps_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" placeholder="Saisissez votre clé API Google Maps ici" />';
    echo '<p class="description">Nécessaire pour le géocodage des adresses (recherche par ville/adresse).</p>';
}

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
            'center_lat' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
            'center_lng' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param );
                },
            ),
            'radius'     => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
                'sanitize_callback' => 'absint',
                'default'           => 25,
            ),
            'is_department_search' => array(
                'required'          => false,
                'validate_callback' => function( $param ) {
                    return in_array( $param, ['true', 'false'], true );
                },
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'false',
            ),
        ),
    ) );
}

/**
 * Récupère les structures locales dans des limites géographiques données ou dans un rayon.
 *
 * @param WP_REST_Request $request La requête REST.
 * @return WP_REST_Response Les données des marqueurs.
 */
function amnesty_get_local_structures_by_bounds( $request ) {
    $south = (float) $request['south'];
    $west  = (float) $request['west'];
    $north = (float) $request['north'];
    $east  = (float) $request['east'];

    $center_lat = isset( $request['center_lat'] ) ? (float) $request['center_lat'] : null;
    $center_lng = isset( $request['center_lng'] ) ? (float) $request['center_lng'] : null;
    $radius     = (int) $request['radius'];
    $is_department_search = ( 'true' === $request['is_department_search'] );

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

    if ( ! $is_department_search && $center_lat !== null && $center_lng !== null && $radius > 0 ) {
        $lat_degree_km = 111.0;
        $lng_degree_km = 111.0 * cos( deg2rad( $center_lat ) );

        $lat_delta = $radius / $lat_degree_km;
        $lng_delta = $radius / $lng_degree_km;

        $min_lat_radius = $center_lat - $lat_delta;
        $max_lat_radius = $center_lat + $lat_delta;
        $min_lng_radius = $center_lng - $lng_delta;
        $max_lng_radius = $center_lng + $lng_delta;

        $args['meta_query'][0]['value'][0] = min( $south, $min_lat_radius );
        $args['meta_query'][0]['value'][1] = max( $north, $max_lat_radius );
        $args['meta_query'][1]['value'][0] = min( $west, $min_lng_radius );
        $args['meta_query'][1]['value'][1] = max( $east, $max_lng_radius );
    }

    $local_structures_ids = get_posts( $args );
    $markers_data = [];

    foreach ( $local_structures_ids as $post_id ) {
        $latitude  = (float) get_field( 'latitude', $post_id );
        $longitude = (float) get_field( 'longitude', $post_id );

        if ( ! $is_department_search && $center_lat !== null && $center_lng !== null && $radius > 0 ) {
            $distance = amnesty_haversine_distance( $center_lat, $center_lng, $latitude, $longitude );
            if ( $distance > $radius ) {
                continue;
            }
        }

        $city      = get_field( 'ville', $post_id );
        $address   = get_field( 'adresse', $post_id );
        $phone     = get_field( 'telephone', $post_id );
        $email     = get_field( 'email', $post_id );

        $post_title = get_the_title( $post_id );
        $post_url = get_permalink( $post_id );

        $thumbnail_url = get_the_post_thumbnail_url( $post_id, [300, 200] );

        $markers_data[] = [
            'id'       => $post_id,
            'title'    => $post_title,
            'url'      => $post_url,
            'image'    => $thumbnail_url ? $thumbnail_url : '',
            '_geoloc'  => [
                'lat' => $latitude,
                'lng' => $longitude,
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

/**
 * Calcule la distance entre deux points sur Terre en utilisant la formule de Haversine.
 *
 * @param float $lat1 Latitude du point 1.
 * @param float $lon1 Longitude du point 1.
 * @param float $lat2 Latitude du point 2.
 * @param float $lon2 Longitude du point 2.
 * @return float Distance en kilomètres.
 */
function amnesty_haversine_distance( $lat1, $lon1, $lat2, $lon2 ) {
    $earth_radius = 6371; // Kilomètres

    $dLat = deg2rad( $lat2 - $lat1 );
    $dLon = deg2rad( $lon2 - $lon1 );

    $a = sin( $dLat / 2 ) * sin( $dLat / 2 ) +
         cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) *
         sin( $dLon / 2 ) * sin( $dLon / 2 );
    $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

    return $earth_radius * $c;
}

add_action( 'rest_api_init', 'amnesty_register_geocode_proxy_rest_route' );

function amnesty_register_geocode_proxy_rest_route() {
    $google_api_key = get_option( 'amnesty_Maps_api_key' );

    if ( empty( $google_api_key ) ) {
        error_log( 'Erreur: La clé API Google Maps est manquante dans les réglages WordPress. Veuillez la configurer dans Réglages > Général.' );
        register_rest_route( 'amnesty/v1', '/geocode-proxy', array(
            'methods'             => 'GET',
            'callback'            => function() {
                return new WP_REST_Response( array( 'status' => 'ERROR', 'error_message' => 'Google Maps API Key is missing in WordPress settings.' ), 500 );
            },
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
        return;
    }

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
    $google_api_key = get_option( 'amnesty_Maps_api_key' ); // Récupère la clé ici aussi

    if ( empty( $google_api_key ) ) {
        return new WP_REST_Response( array( 'status' => 'ERROR', 'error_message' => 'Google Maps API Key is missing.' ), 500 );
    }

    $address = sanitize_text_field( $request['address'] );
    $google_geocode_url = add_query_arg(
        array(
            'address' => urlencode( $address ),
            'key'     => $google_api_key,
            'components' => 'country:FR',
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
