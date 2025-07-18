<?php

namespace transformers;

use Type;

class PaysTransformer extends DocTransformer {

    public function parse($prismicDoc): array {
		$wp_post = parent::parse( $prismicDoc );

		$wp_post['post_type'] = Type::get_wp_post_type(\Type::PAYS);

		$wp_post['tax_terms']['location'] = [ \TaxMapper::mapCountry( $prismicDoc['uid'] ) ];

        return $wp_post;
    }
}
