<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Features\Letter_Recipients;

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_remove_recipient_meta' ) ) {
	/**
	 * Remove recipient meta if the recipient block is removed.
	 *
	 * @package Amnesty\Blocks
	 *
	 * @param integer $post_id Current post id.
	 * @param WP_Post $post    Current post object.
	 *
	 * @return void
	 */
	function amnesty_remove_recipient_meta( $post_id, $post ) {
		$block_identifier = '<!-- wp:amnesty-core/block-recipients /-->';

		if ( strpos( $post->post_content, $block_identifier ) !== false ) {
			return;
		}

		$meta_to_remove = [
			'recipients_refresh',
		];

		foreach ( $meta_to_remove as $meta_key ) {
			if ( get_post_meta( $post_id, $meta_key, true ) ) {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}
}

add_action( 'save_post', __NAMESPACE__ . '\\amnesty_remove_recipient_meta', 100, 2 );


if ( ! function_exists( __NAMESPACE__ . '\\amnesty_remove_stale_recipient_meta' ) ) {
	/**
	 * Remove recipient meta if the recipient block is removed.
	 *
	 * @package Amnesty\Blocks
	 *
	 * @param integer $post_id Current post id.
	 *
	 * @return void
	 */
	function amnesty_remove_stale_recipient_meta( $post_id ) {
		if ( get_post_meta( $post_id, 'recipients_refreshed', true ) === 'true' &&
			get_post_meta( $post_id, 'recipients', true ) ) {
			delete_post_meta( $post_id, 'recipients' );
		}
	}
}

add_action( 'save_post', __NAMESPACE__ . '\\amnesty_remove_stale_recipient_meta', 100 );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_register_recipient_meta_as_copyable' ) ) {
	/**
	 * Register recipient metadata as copyable using multilingualpress
	 *
	 * @param array<int,string> $keys copyable meta keys
	 *
	 * @return array<int,string>
	 */
	function amnesty_register_recipient_meta_as_copyable( array $keys ): array {
		return array_merge(
			$keys,
			[
				'recipients_refresh',
				'recipients_refreshed',
				'recipients',
			]
		);
	}
}

add_filter( 'amnesty_copyable_meta_keys', __NAMESPACE__ . '\\amnesty_register_recipient_meta_as_copyable' );

if ( ! function_exists( __NAMESPACE__ . '\\amnesty_register_recipient_meta' ) ) {
	/**
	 * Register recipient meta keys with WordPress
	 *
	 * @return void
	 */
	function amnesty_register_recipient_meta(): void {
		$args = [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		];

		register_meta( 'post', 'recipients', $args );
		register_meta( 'post', 'recipients_refresh', $args );
		register_meta( 'post', 'recipients_refreshed', $args );
	}
}

add_action( 'init', __NAMESPACE__ . '\\amnesty_register_recipient_meta' );
