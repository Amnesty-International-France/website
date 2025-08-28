<?php

declare(strict_types=1);

function register_user_to_urgent_action( string $type, string $user_id ): WP_REST_Response {
	$register_user_urgent_action = insert_urgent_action(
		$type,
		$user_id,
		wp_date( 'Y-m-d' ),
		false
	);

	if ( ! $register_user_urgent_action ) {
		return new WP_REST_Response(
			[
				'status'  => 'error',
				'message' => 'Une erreur est survenue lors de l\'enregistrement.',
			],
			500
		);
	}

	return new WP_REST_Response(
		[
			'status'  => 'success',
			'message' => 'Vous êtes bien inscrit(e) au réseau actions urgentes.',
		],
		200
	);
}

function post_urgent_action( WP_REST_Request $request ) {
	if (
		! is_user_logged_in() &&
		(
			! $request->get_header( 'X-Amnesty-UA-Nonce' ) ||
			! wp_verify_nonce( $request->get_header( 'X-Amnesty-UA-Nonce' ), 'amnesty_sign_urgent_action' )
		)
	) {
		return new WP_Error(
			'invalid_nonce',
			'Security check failed. Please try again.',
			[ 'status' => 403 ]
		);
	}

	$type = sanitize_text_field( $request->get_param( 'type' ) );

	if ( ! \in_array( $type, [ 'email', 'sms', 'militant' ], true ) ) {
		return new WP_Error( 'invalid_type', 'Type not valid.', [ 'status' => 400 ] );
	}

	$email = sanitize_email( $request->get_param( 'email' ) );

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', 'Email not valid.', [ 'status' => 400 ] );
	}

	$local_user = get_local_user( $email );

	if ( $local_user ) {
		$user_id               = $local_user->id;
		$action_already_signed = urgent_action_already_signed( $type, $user_id );

		if ( ! $action_already_signed ) {
			register_user_to_urgent_action( $type, $user_id );
		}

		return new WP_REST_Response(
			[
				'status'  => 'already_signed',
				'message' => 'Vous êtes déjà inscrit(e) au réseau actions urgentes.',
			],
			409
		);
	}

	$civility    = sanitize_text_field( $request->get_param( 'civility' ) );
	$firstname   = sanitize_text_field( $request->get_param( 'firstname' ) );
	$lastname    = sanitize_text_field( $request->get_param( 'lastname' ) );
	$postal_code = sanitize_text_field( $request->get_param( 'zipcode' ) );
	$country     = sanitize_text_field( $request->get_param( 'country' ) );
	$phone       = sanitize_text_field( $request->get_param( 'tel' ) );

	$new_user_id = insert_user( $civility, $firstname, $lastname, $email, $country, $postal_code, $phone );

	if ( ! $new_user_id ) {
		return new WP_REST_Response(
			[
				'status'  => 'error',
				'message' => 'Une erreur est survenue lors de l\'enregistrement.',
			],
			500
		);
	}

	return register_user_to_urgent_action( $type, (string) $new_user_id );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'humanity/v1',
			'/register-urgent-action',
			[
				'methods'             => 'POST',
				'callback'            => 'post_urgent_action',
				'permission_callback' => '__return_true',
			]
		);
	}
);

function amnesty_ua_enqueue_scripts() {
	$handle = 'urgent-register-form-js';
	$src    = site_url( '/private/src/scripts/modules/Form/urgent-register-form.js' );

	wp_enqueue_script( $handle, $src );

	$nonce_action = is_user_logged_in() ? 'wp_rest' : 'amnesty_sign_urgent_action';

	wp_localize_script(
		$handle,
		'UrgentRegisterData',
		[
			'url'          => rest_url( 'humanity/v1/register-urgent-action' ),
			'nonce'        => wp_create_nonce( $nonce_action ),
			'is_connected' => is_user_logged_in(),
		]
	);
}
add_action( 'wp_enqueue_scripts', 'amnesty_ua_enqueue_scripts' );
