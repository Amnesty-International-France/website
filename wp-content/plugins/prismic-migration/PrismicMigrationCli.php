<?php

use transformers\DocTransformerFactory;

/**
 * Implements prismic-migration command
 */
class PrismicMigrationCli {

	public static bool $dryrun = false;

	public static bool $forceMod = false;

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
	 * [--force]
	 * : Force the import, will replace the existing content (not medias)
	 *
	 * [--since=<value>]
	 * : Define a minimum import date
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
	 *     # Import all documents of the content 'news' since a minimum date
	 *     $ wp prismic-migration --type=news --since=2025-01-01
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$type = Type::tryFrom( $assoc_args['type'] );

		if( !$type ) {
			WP_CLI::error( 'Invalid content type: ' . $assoc_args['type'] );
			return;
		}

		self::$dryrun = isset( $assoc_args['dry-run'] ) && $assoc_args['dry-run'] === true;
		if ( self::$dryrun ) {
			WP_CLI::log('dry-run mod activated');
		}

		self::$forceMod = isset( $assoc_args['force'] ) && $assoc_args['force'] === true;
		if( self::$forceMod ) {
			WP_CLI::log('force mod activated, existing content will be replaced.');
			WP_CLI::log('Starting in 5 seconds, you can still cancel...');
			sleep(5);
		}

		$imported = 0;

		$since = isset( $assoc_args['since']) ? new \DateTime( $assoc_args['since'] ) : null;

		$fetcher = new PrismicFetcher();
		$prismic_docs = isset( $assoc_args['id'] )
			? $fetcher->fetch_article( $assoc_args['id'] )
			: $fetcher->fetch(limit: $assoc_args['limit'], ordering: Ordering::from($assoc_args['ordering']), type: $type, since: $since);

		$progress = WP_CLI\Utils\make_progress_bar( 'Importing documents', count($prismic_docs) );
		foreach ( $prismic_docs as $doc ) {
			$docType = Type::tryFrom( $doc['type'] );
			if( $docType === null) {
				WP_CLI::warning( 'Document type not supported : ' . $doc['type'] . ' (' . $doc['uid'] . ')' );
			}

			if( ! isset($doc['uid']) ) {
				//WP_CLI::warning( 'Document without uid : ' . $doc['id'] );
				continue;
			}

			$id = $this->post_exists_by_slug_and_type($doc['uid'], $docType);
			if ( !self::$forceMod && $id !== false ) {
				$progress->tick();
				continue;
			}

			$transformer = DocTransformerFactory::getTransformer( $docType );

			$wp_post = $transformer->parse( $doc );
			$wp_post['post_content'] = wp_slash(serialize_blocks(array_merge(...$wp_post['post_content'])));

			if ( ! self::$dryrun ) {
				if ( self::$forceMod && $id !== false ) {
					$wp_post['ID'] = $id;
					$postId = wp_update_post( $wp_post );
				} else {
					$postId = wp_insert_post( $wp_post );
				}

				if ( is_wp_error($postId) ) {
					WP_CLI::warning( 'Error inserting post: ' . $postId->get_error_message() );
					$progress->tick();
					continue;
				}

				foreach ( $wp_post['tax_terms'] ?? [] as $term => $ids ) {
					wp_set_object_terms( $postId, $ids, $term );
				}

				$featured_image = $transformer->featuredImage( $doc );
				if ( $featured_image !== false ) {
					try {
						$attachment_id = FileUploader::uploadMedia( $featured_image['imageUrl'], $featured_image['legend'], $featured_image['description'], $featured_image['alt'] );
						set_post_thumbnail( $postId, $attachment_id );
					} catch( Exception $e ) {}
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

	function post_exists_by_slug_and_type(string $slug, Type $type): int|bool {
		$query = new WP_Query([
			'name' => sanitize_title($slug),
			'post_type' => Type::get_wp_post_type($type),
			'post_status' => 'any',
			'fields' => 'ids',
		]);

		if( ! empty( $query->posts ) ) {
			return $query->posts[0];
		}

		return false;
	}
}
