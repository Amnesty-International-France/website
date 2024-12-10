<?php

declare( strict_types = 1 );

namespace Amnesty\Branding\Blocks;

use WP_Block_Type_Registry;

add_filter( 'allowed_block_types_all', __NAMESPACE__ . '\\amnesty_filter_allowed_blocks', 100 );

/**
 * Unregister unused or unwanted blocks
 *
 * @package Amnesty\Blocks
 *
 * @return array<int,string>
 */
function amnesty_filter_allowed_blocks(): array {
	/**
	 * Core return type hint is incorrect, so we overload
	 *
	 * @var array<string,\WP_Block_Type>
	 */
	$all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$namespaces = [ 'yoast', 'yoast-seo', 'woocommerce' ];
	$denylist   = [];
	$hidden     = [
		'core/archives',
		'core/audio',
		'core/avatar',
		'core/calendar',
		'core/categories',
		'core/comments',
		'core/comments-pagination',
		'core/comment-content',
		'core/comment-edit-link',
		'core/comment-reply-link',
		'core/comment-template',
		'core/details',
		'core/file',
		'core/footnotes',
		'core/freeform',
		'core/gallery',
		'core/latest-comments',
		'core/loginout',
		'core/media-text',
		'core/more',
		'core/navigation',
		'core/nextpage',
		'core/page-list',
		'core/post-author',
		'core/post-author-biography',
		'core/post-author-name',
		'core/post-comments',
		'core/post-comments-form',
		'core/post-content',
		'core/post-date',
		'core/post-excerpt',
		'core/post-featured-image',
		'core/post-navigation-link',
		'core/post-template',
		'core/post-terms',
		'core/post-title',
		'core/preformatted',
		'core/query',
		'core/query-title',
		'core/read-more',
		'core/rss',
		'core/search',
		'core/site-logo',
		'core/site-tagline',
		'core/site-title',
		'core/tag-cloud',
		'core/term-description',
		'core/verse',
		'amnesty-core/image-block',
	];


	foreach ( $all_blocks as $name => $type ) {
		list( $vendor, $block ) = explode( '/', $name );

		// disallowed vendors
		if ( in_array( $vendor, $namespaces, true ) ) {
			unset( $all_blocks[ $name ] );
			continue;
		}

		// disallowed blocks
		if ( in_array( $name, $denylist, true ) ) {
			unset( $all_blocks[ $name ] );
			continue;
		}

		// blocks hidden from the inserter
		if ( in_array( $name, $hidden, true ) ) {
			$all_blocks[ $name ]->supports['inserter'] = false;
			continue;
		}
	}

	return array_keys( $all_blocks );
}
