<?php

use transformers\DocTransformerFactory;

class PrismicMigrationCli {

	public static bool $dryrun = false;

	public function __invoke( $args, $assoc_args ) {
		self::$dryrun = false;
		$imported = 0;
		if ( self::$dryrun ) {
			WP_CLI::log('dry-mod activated');
		}
		$prismic_docs = (new PrismicFetcher())->fetch(type: Type::NEWS);

		$progress = WP_CLI\Utils\make_progress_bar( 'Importing documents', count($prismic_docs) );
		foreach ( $prismic_docs as $doc ) {
			if ( $this->post_exists_by_slug($doc['uid']) ) {
				$progress->tick();
				continue;
			}
			$transformer = DocTransformerFactory::getTransformer( $doc['type'] );

			$wp_post = $transformer->parse( $doc );

		if ( ! self::$dryrun ) {
				$postId = wp_insert_post( $wp_post );

				if ( is_wp_error($postId) ) {
					WP_CLI::warning( 'Error inserting post: ' . $postId->get_error_message() );
					$progress->tick();
					continue;
				}

				foreach ( $wp_post['terms'] as $term => $ids ) {
					wp_set_object_terms( $postId, $ids, $term );
				}

				$featured_image = $transformer->featuredImage( $doc );
				if ( $featured_image !== false ) {
					$attachment_id = FileUploader::uploadMedia( $featured_image['imageUrl'], $featured_image['legend'], $featured_image['description'], $featured_image['alt'] );
					set_post_thumbnail( $postId, $attachment_id );
				}

				if ( isset($wp_post['relatedArticles']) ) {
					update_post_meta( $postId, '_related_posts_selected', $wp_post['relatedArticles'] );
				}
			}

			$imported++;
			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( 'Migration successful: ' . $imported . ' documents imported.' );
	}


	function post_exists_by_slug(string $slug): WP_Post|bool {
		$query = new WP_Query([
			'name' => $slug,
			'post_type' => 'any',
			'post_status' => 'any',
			'fields' => 'ids'
		]);
		return ! empty( $query->posts );
	}
}
