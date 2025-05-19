<?php

use transformers\DocTransformerFactory;

/**
 * Implements prismic-migration command
 */
class PrismicMigrationCli {

	public static bool $dryrun = false;

	/**
	 * Fetch documents from Prismic Repository and import them into Wordpress.
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<value>]
	 * : If you want to limit the number of documents to fetch. Defaults to -1.
	 * ---
	 * default: -1
	 * ---
	 *
	 * [--type=<type>]
	 * : Specify the content type to import.
	 * ---
	 * default:
	 * ---
	 *
	 * [--ordering=<value>]
	 * : Set the order of documents by last_publication_date to ASC or DESC.
	 * ---
	 * default: DESC
	 * options:
	 *   - ASC
	 *   - DESC
	 * ---
	 *
	 * [--dry-run]
	 * : Starts in dry-run mode (does not import)
	 *
	 * [--id=<value>]
	 * : Imports a document by his id
	 *
	 * ## EXAMPLES
	 *
	 *     # Import all documents of all content
	 *     $ wp prismic-migration
	 *
	 *     # Import all documents of the content 'news'
	 *     $ wp prismic-migration --type=news
	 *
	 *     # Import 100 oldest documents of the content 'news'
	 *     $ wp prismic-migration --type=news --limit=100
	 *
	 *     # Import all documents of the content 'news' in dry-run mode (doesn't import the data)
	 *     $ wp prismic-migration --type=news --dry-run
	 *
	 *     # Import a document referenced by his prismic id
	 *     $ wp prismic-migration --id=LDR4LF
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$type = Type::tryFrom( strtolower( $assoc_args['type'] ) );

		if( !$type ) {
			WP_CLI::error( 'Invalid content type: ' . $assoc_args['type'] );
			return;
		}

		self::$dryrun = isset( $assoc_args['dry-run'] ) && $assoc_args['dry-run'] === true;
		if ( self::$dryrun ) {
			WP_CLI::log('dry-mod activated');
		}

		$imported = 0;

		$fetcher = new PrismicFetcher();
		$prismic_docs = isset( $assoc_args['id'] )
			? $fetcher->fetch_article( $assoc_args['id'] )
			: $fetcher->fetch(limit: $assoc_args['limit'], ordering: Ordering::from($assoc_args['ordering']), type: $type);

		$progress = WP_CLI\Utils\make_progress_bar( 'Importing documents', count($prismic_docs) );
		foreach ( $prismic_docs as $doc ) {
			$docType = Type::tryFrom( $doc['type'] );
			if( $docType === null) {
				WP_CLI::warning( 'Document type not supported : ' . $doc['type'] . ' (' . $doc['uid'] . ')' );
			}

			if ( $this->post_exists_by_slug($doc['uid'], $docType) ) {
				$progress->tick();
				continue;
			}
			$transformer = DocTransformerFactory::getTransformer( $docType );

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

				$seo_og = $transformer->getSeoAndOgData( $doc );
				foreach ( $seo_og as $metaKey => $metaValue ) {
					update_post_meta( $postId, $metaKey, $metaValue );
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

	function post_exists_by_slug(string $slug, Type $type): WP_Post|bool {
		$query = new WP_Query([
			'name' => $slug,
			'post_type' => Type::get_wp_post_type($type),
			'post_status' => 'any',
			'fields' => 'ids'
		]);
		return ! empty( $query->posts );
	}
}
