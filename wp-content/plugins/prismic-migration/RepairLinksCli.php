<?php

use utils\LinksUtils;

/**
 * Implements repair-links command
 */
class RepairLinksCli {

	/**
	 * Replaces all placeholders placed by prismic-migration command by the good value if he can.
	 *
	 * @when after_wp_load
	 */
	public function __invoke() {
		WP_CLI::log( 'Repairing links in pages...' );

		$args = array(
			'post_type' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);
		$post_ids = get_posts( $args );
		if ( $post_ids ) {
			$progress = WP_CLI\Utils\make_progress_bar( 'Repairing links', count($post_ids) );
			$total = 0;
			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );

				$related = get_post_meta( $post_id, '_related_posts_selected', true );
				if( $related !== false && !empty( $related ) && is_array( $related ) ) {
					foreach ( $related as $key => $related_article ) {
						if(is_string( $related_article )) {
							$count = LinksUtils::repairLinks( $related_article );
							$related[$key] = (int) $related_article;
							$total += $count;
						}
					}
					update_post_meta( $post_id, '_related_posts_selected', $related );
				}

				$content = $post->post_content;
				$count = LinksUtils::repairLinks( $content );
				if( $count > 0 ) {
					$total += $count;
					$updatedPost = [
						'ID' => $post_id,
						'post_content' => $content,
					];
					wp_update_post( $updatedPost );
				}
				$progress->tick();
			}
			$progress->finish();
			WP_CLI::log( "$total links updated." );
		} else {
			WP_CLI::log( 'No posts found.' );
		}
	}
}
