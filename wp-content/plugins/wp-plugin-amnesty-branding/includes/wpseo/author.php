<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Seo;

use WP_REST_Response;
use Yoast\WP\SEO\Presenters\Abstract_Presenter;
use Yoast\WP\SEO\Presenters\Meta_Author_Presenter;

// remove author data from <head>

add_filter( 'wpseo_enhanced_slack_data', '__return_empty_array', 100 );
add_filter( 'wpseo_schema_person_user_id', '__return_zero', 100 );
add_filter( 'wpseo_schema_person_social_profiles', '__return_null', 100 ); // @phpstan-ignore-line
add_filter( 'wpseo_opengraph_author_facebook', '__return_empty_string', 100 );
add_filter( 'wpseo_twitter_creator_account', '__return_empty_string', 100 );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_wpseo_remove_author_presenter' ) ) {
	/**
	 * Remove author output from Yoast
	 *
	 * @package Amnesty\Plugins\Yoast
	 *
	 * @param array<int,Abstract_Presenter> $presenters the list of frontend presenters
	 *
	 * @return array<int,Abstract_Presenter>
	 */
	function amnesty_wpseo_remove_author_presenter( array $presenters ): array {
		return array_filter(
			$presenters,
			fn ( Abstract_Presenter $presenter ): bool => ! is_a( $presenter, Meta_Author_Presenter::class )
		);
	}
}

add_filter( 'wpseo_frontend_presenters', __NAMESPACE__ . '\\amnesty_wpseo_remove_author_presenter' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_wpseo_remove_author_schema' ) ) {
	/**
	 * Remove author data from Yoast schema.org data
	 *
	 * @package Amnesty\Plugins\Yoast
	 *
	 * @param array $piece the schema.org.data
	 *
	 * @return array
	 */
	function amnesty_wpseo_remove_author_schema( array $piece ): array {
		if ( is_single() ) {
			unset( $piece['author'] );
		}

		return $piece;
	}
}

add_filter( 'wpseo_schema_article', __NAMESPACE__ . '\\amnesty_wpseo_remove_author_schema' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_oembed_remove_author_data' ) ) {
	/**
	 * Remove author data from oEmbed response data
	 *
	 * @param array $data the oEmbed data
	 *
	 * @return array
	 */
	function amnesty_oembed_remove_author_data( array $data ): array {
		unset( $data['author_name'], $data['author_url'] );
		return $data;
	}
}

add_filter( 'oembed_response_data', __NAMESPACE__ . '\\amnesty_oembed_remove_author_data' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_rest_api_remove_author_data' ) ) {
	/**
	 * Remove author data from public API responses
	 *
	 * @param WP_REST_Response $response the response object
	 *
	 * @return WP_REST_Response
	 */
	function amnesty_rest_api_remove_author_data( WP_REST_Response $response ): WP_REST_Response {
		if ( wp_get_current_user()->ID ) {
			return $response;
		}

		$data = $response->get_data();
		unset( $data['author'], $data['authorName'] );
		$response->set_data( $data );

		return $response;
	}
}

add_filter( 'rest_prepare_post', __NAMESPACE__ . '\\amnesty_rest_api_remove_author_data' );
