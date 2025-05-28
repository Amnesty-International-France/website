<?php

namespace transformers;

use Type;

class DossierTransformer extends DocTransformer {

	public function parse($prismicDoc): array {
		$wp_post = parent::parse($prismicDoc);

		$wp_post['post_type'] = Type::get_wp_post_type(\Type::DOSSIER);
		$wp_post['post_category'] = $this->getCategories(array('dossiers'));

		$terms = $this->getTerms( $prismicDoc );
		$wp_post['terms'] = [
			'location' => array_filter( array_column($terms['countries'], 'slug'), static fn($s) => $s !== null ),
			'combat' => array_filter( array_column($terms['combats'], 'slug'), static fn($s) => $s !== null )
		];

		if( isset($wp_post['status']) && $wp_post['status'] === 'archivé' ) {
			$wp_post['post_status'] = 'private';
		}
		return $wp_post;
	}

}
