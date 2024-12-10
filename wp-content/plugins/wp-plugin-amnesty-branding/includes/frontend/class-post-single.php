<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Frontend;

/**
 * Actions that modify the post single template
 */
class Post_Single {

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_action( 'amnesty_after_published_date', [ $this, 'amnesty_after_published_date' ] );
	}

	/**
	 * Output additional content after the published date
	 *
	 * @return void
	 */
	public function amnesty_after_published_date(): void {
		require dirname( AIBRAND_FILE ) . '/views/partials/single/docref.php';
	}

}
