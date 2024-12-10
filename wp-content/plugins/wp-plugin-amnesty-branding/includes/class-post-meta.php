<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

/**
 * Post meta registration, etc.
 */
class Post_Meta {

	/**
	 * Construct the class
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
	}

	/**
	 * Register post meta
	 *
	 * @return void
	 */
	public function register_meta(): void {
		// amnesty.eu version of AI Index Number
		register_meta(
			'post',
			'document_ref',
			[
				'type'           => 'string',
				'object_subtype' => 'post',
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);

		// register field for major updates to posts
		register_meta(
			'post',
			'amnesty_updated',
			[
				/* translators: [admin] */
				'description'    => __( 'For use when an item has had a major update', 'aibrand' ),
				'type'           => 'string',
				'object_subtype' => 'post',
				'single'         => true,
				'show_in_rest'   => true,
			]
		);
	}

}
