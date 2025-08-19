<?php

namespace transformers;


class CommuniquePresseTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = (new PageFroideTransformer())->parse( $prismicDoc );

		$wp_post['post_type'] = \Type::get_wp_post_type(\Type::COMMUNIQUE_PRESSE);

		$wp_post['meta_input']['status'] = $prismicDoc['data']['status'] ?? '';
		$wp_post['meta_input']['_status'] = 'field_68a44f83744ae';

		return $wp_post;
	}

}
