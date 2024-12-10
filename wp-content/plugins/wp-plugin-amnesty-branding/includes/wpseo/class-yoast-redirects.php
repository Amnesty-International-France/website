<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Seo;

use WPSEO_Redirect_Option;
use Yoast_Notification;

/**
 * Modify Yoast's automatic redirect creation
 *
 * @package Amnesty\Plugins\Yoast
 */
class Yoast_Redirects {

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_filter( 'sanitize_post_meta__yoast_post_redirect_info', [ $this, 'postmeta' ] );
		add_filter( 'update_term_metadata', [ $this, 'termmeta' ], 10, 4 );
		add_filter( 'pre_update_option_wpseo-premium-redirects-base', [ $this, 'options' ] );
		add_filter( 'Yoast\WP\SEO\save_redirects', [ $this, 'redirects' ] );
		add_filter( 'yoast_notifications_before_storage', [ $this, 'notifications' ] );
	}

	/**
	 * Prevent Yoast from adding language prefix to redirects
	 * in multisite (postmeta table)
	 *
	 * @param array<string,mixed> $value the redirect data
	 *
	 * @return array<string,mixed>
	 */
	public function postmeta( array $value ) {
		if ( ! $this->should_filter() ) {
			return $value;
		}

		// only if we have usable data
		if ( ! isset( $value['origin'], $value['target'] ) ) {
			return $value;
		}

		// strip the language prefix
		$value['origin'] = preg_replace( '/^[a-z]{2}\/(.*)/', '$1', $value['origin'] );
		$value['target'] = preg_replace( '/^[a-z]{2}\/(.*)/', '$1', $value['target'] );

		return $value;
	}

	/**
	 * Prevent Yoast from adding language prefix to redirects
	 * in multisite (termmeta table)
	 *
	 * @param null   $check      whether to bypass meta update
	 * @param int    $term_id    the ID of the term whose meta is being modified
	 * @param string $meta_key   the meta key of the value being modified
	 * @param mixed  $meta_value the meta value being set
	 *
	 * @return null|bool
	 */
	public function termmeta( $check, int $term_id, string $meta_key, $meta_value ) {
		if ( ! $this->should_filter() ) {
			return $check;
		}

		// only target redirect data
		if ( '_yoast_term_redirect_info' !== $meta_key ) {
			return $check;
		}

		// only if we have usable data
		if ( ! isset( $meta_value['origin'], $meta_value['target'] ) ) {
			return $check;
		}

		$target_has_prefix = 1 === preg_match( '/^([a-z]{2})\//', $meta_value['target'], $match_target );

		// redirect is fine - no lang prefix present
		if ( ! $target_has_prefix ) {
			return $check;
		}

		// data used during inspection
		$domain = wp_parse_url( get_site_meta( 1, 'siteurl', true ), PHP_URL_HOST );

		// retrieve prefix
		$prefix_target = $match_target[1] ?? null;
		$site_target   = false;

		if ( $prefix_target ) {
			$site_target = get_site_by_path( $domain, sprintf( '/%s/', $prefix_target ) );
		}

		// prefix isn't a site prefix, safe to allow
		if ( ! $site_target ) {
			return $check;
		}

		$prefixless_target = preg_replace( '/^[a-z]{2}\//', '', $meta_value['target'] );

		// yoast is inadvertently creating a redirect loop
		if ( $meta_value['origin'] === $prefixless_target ) {
			return false;
		}

		return $check;
	}

	/**
	 * Prevent Yoast from adding language prefix to redirects
	 * in multisite (options table)
	 *
	 * @param array<int,array<string,mixed>> $values the list of redirects
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function options( array $values ) {
		if ( ! $this->should_filter() ) {
			return $values;
		}

		// we have an array of redirects here
		foreach ( $values as &$value ) {
			if ( $this->filter_option( $value ) ) {
				// strip language prefix
				$value['origin'] = preg_replace( '/^[a-z]{2}\/(.*)/', '$1', $value['origin'] );
				$value['url']    = preg_replace( '/^[a-z]{2}\/(.*)/', '$1', $value['url'] );
			}
		}

		return $values;
	}

	/**
	 * Prevent Yoast from adding erroneous redirects
	 *
	 * Yoast strips the language prefix from the origin URI but not
	 * the target, so it thinks that they are different, and therefore
	 * that a redirect needs to be added. It does not.
	 *
	 * @param array<int,array<string,mixed>> $redirects the list of redirects that are being saved
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function redirects( array $redirects ): array {
		if ( ! $this->should_filter() ) {
			return $redirects;
		}

		$old_redirects = (array) get_option( WPSEO_Redirect_Option::OPTION );
		$start_count   = count( $old_redirects );
		$current_count = count( $redirects );

		// a redirect(s) is being deleted
		if ( $current_count < $start_count ) {
			return $redirects;
		}

		// ensure array pointer is at start
		reset( $redirects );

		// fast forward the pointer to the first item we need to inspect
		// (we do this because the array key cannot be relied upon)
		for ( $i = 0; $i < $start_count; $i++ ) {
			next( $redirects );
		}

		// data used during inspection
		$domain = wp_parse_url( site_url(), PHP_URL_HOST );

		// inspect each of the newly-added redirects
		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( null !== ( $key = key( $redirects ) ) ) {
			if ( $this->filter_redirect( current( $redirects ), $domain ) ) {
				unset( $redirects[ $key ] );
			}

			next( $redirects );
		}

		return $redirects;
	}

	/**
	 * Remove some superfluous Yoast notifications (for prevented redirects)
	 *
	 * @param array<int,\Yoast_Notification> $notifications the list of notifications
	 *
	 * @return array<int,\Yoast_Notification>
	 */
	public function notifications( array $notifications ): array {
		if ( ! $this->should_filter() ) {
			return $notifications;
		}

		foreach ( $notifications as $index => $notification ) {
			if ( $this->filter_notification( $notification ) ) {
				// the redirect will not have been added - remove it
				unset( $notifications[ $index ] );
			}
		}

		return $notifications;
	}

	/**
	 * Check whether the filter(s) should run
	 *
	 * @return bool
	 */
	protected function should_filter(): bool {
		// don't target non-multisites
		if ( ! is_multisite() ) {
			return false;
		}

		// don't target sites not using multilingualpress
		if ( ! function_exists( 'is_multilingualpress_enabled()' ) || ! is_multilingualpress_enabled() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether a redirect should be modified
	 *
	 * @param array<string,mixed> $value the redirect
	 *
	 * @return bool
	 */
	protected function filter_option( array $value ): bool {
		// only if we have usable data
		if ( ! isset( $value['origin'], $value['url'] ) ) {
			return false;
		}

		// only target plain redirects
		if ( ! isset( $value['format'] ) || 'plain' !== $value['format'] ) {
			return false;
		}


		return true;
	}

	/**
	 * Check whether a redirect should be removed
	 *
	 * @param array<string,mixed> $redirect the redirect to check
	 * @param string              $domain   the site domain
	 *
	 * @return bool
	 */
	protected function filter_redirect( array $redirect, string $domain ): bool {
		// no workable url for the item
		if ( ! isset( $redirect['url'] ) ) {
			return false;
		}

		// inspect
		// the new url doesn't have a prefix
		if ( 1 !== preg_match( '/^([a-z]{2})\//', $redirect['url'], $match ) ) {
			return false;
		}

		// get the prefix
		$code = $match[1] ?? null;

		// somehow didn't capture the prefix
		if ( ! $code ) {
			return false;
		}

		// confirm that prefix was a lang code
		$site = get_site_by_path( $domain, sprintf( '/%s/', $code ) );

		// it wasn't a language code prefix
		if ( ! is_a( $site, \WP_Site::class ) ) {
			return false;
		}

		// strip the prefix
		$stripped = preg_replace( '/^[a-z]{2}\//', '', $redirect['url'] );

		// is the target the different to the origin
		if ( $redirect['origin'] !== $stripped ) {
			return false;
		}

		// it's a redirect loop
		return true;
	}

	/**
	 * Check whether a notification should be removed
	 *
	 * @param \Yoast_Notification $notification the notification to check
	 *
	 * @return bool
	 */
	protected function filter_notification( Yoast_Notification $notification ): bool {
		$old_url_regex = '/' . esc_html__( 'Old URL:', 'wordpress-seo-premium' ) . '\s*<a[^>]*>([^<]*)<\/a>/';
		$new_url_regex = '/' . esc_html__( 'New URL:', 'wordpress-seo-premium' ) . '\s*<a[^>]*>([^<]*)<\/a>/';

		// slug changes come through as the 'updated' notification type
		if ( 'updated' !== $notification->get_type() ) {
			return false;
		}

		$message = $notification->to_array()['message'] ?? null;

		// unusable data
		if ( ! $message ) {
			return false;
		}

		preg_match( $old_url_regex, $message, $old_url );
		preg_match( $new_url_regex, $message, $new_url );

		// unusable data
		if ( ! isset( $old_url[1], $new_url[1] ) ) {
			return false;
		}

		// they are different - a working redirect
		if ( $old_url[1] !== $new_url[1] ) {
			return false;
		}

		return true;
	}

}
