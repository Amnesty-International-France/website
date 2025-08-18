<?php

namespace transformers;

use Type;

class ActionTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = (new PageFroideTransformer())->parse( $prismicDoc );

		$wp_post['post_type'] = \Type::get_wp_post_type(Type::ACTION);

		$parent_page = get_page_by_path('actions', OBJECT, 'page');

		if( $parent_page ) {
			$wp_post['post_parent'] = $parent_page->ID;
		} else {
			if ( ! \PrismicMigrationCli::$dryrun) {
				$parent = wp_insert_post([
					'post_title' => 'Actions',
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_content' => '',
					'post_name' => 'actions'
				]);
				if( ! is_wp_error( $parent )) {
					$wp_post['post_parent'] = $parent;
				}
			}
		}

		return $wp_post;
	}

}
