<?php

namespace transformers;

use Type;

class ActionMobilisationTransformer extends DocTransformer
{

	public function parse($prismicDoc): array
	{
		$wp_post = (new PageFroideTransformer())->parse( $prismicDoc );

		$wp_post['post_type'] = \Type::get_wp_post_type(Type::ACTION_MOBILISATION);

		$parent_page = get_page_by_path('actions-mobilisation', OBJECT, 'page');

		if( $parent_page ) {
			$wp_post['post_parent'] = $parent_page->ID;
		} else {
			if ( ! \PrismicMigrationCli::$dryrun) {
				$parent = wp_insert_post([
					'post_title' => 'Actions de Mobilisation',
					'post_type' => 'page',
					'post_status' => 'publish',
					'post_content' => '',
					'post_name' => 'actions-mobilisation'
				]);
				if( ! is_wp_error( $parent )) {
					$wp_post['post_parent'] = $parent;
				}
			}
		}

		return $wp_post;
	}
}
