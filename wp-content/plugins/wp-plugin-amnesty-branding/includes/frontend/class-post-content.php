<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Frontend;

/**
 * Post content-related filters
 */
class Post_Content {

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_filter( 'amnesty_the_content', [ $this, 'amnesty_the_content' ] );
	}

	/**
	 * Filter post content
	 *
	 * @param string $content the post content
	 *
	 * @return string
	 */
	public function amnesty_the_content( string $content ): string {
		$content = $this->add_recipients_to_content( $content );
		return $content;
	}

	/**
	 * Prepend recipients to post content
	 *
	 * @param string $content the post content
	 *
	 * @return string
	 */
	protected function add_recipients_to_content( string $content ): string {
		ob_start();
		require dirname( AIBRAND_FILE ) . '/views/partials/single/recipients.php';
		return ob_get_clean() . $content;
	}

}
