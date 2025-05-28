<?php

namespace transformers;

use Type;
use utils\LinksUtils;
use utils\ReturnType;

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

		if( isset($prismicDoc['data']['relatedResources']) ) {
			$ids = [];
			foreach ( $prismicDoc['data']['relatedResources'] as $related ) {
				$content = $related['relatedcontent'];
				if( isset($content['type'], $content['uid']) && $content['type'] === 'rapport' ) {
					$ids[] = LinksUtils::generatePlaceHolderDoc('rapport', $content['uid'], ReturnType::ID);
				}
			}
			if( !empty($ids) ) {
				$download_block = [
					'blockName' => 'amnesty-core/download-go-further',
					'attrs' => [
						'title' => 'POUR ALLER PLUS LOIN',
						'fileIds' => $ids
					],
					'innerContent' => []
				];
				$wp_post['post_content'][] = [$download_block];
			}
		}
		return $wp_post;
	}

}
